<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Time;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\Application;
use Illuminate\Support\Facades\Response;

class AdminContentsController extends Controller
{
    public function AdminAttendanceList(Request $request)
    {
        // 日付取得
        $date = $request->date ? Carbon::parse($request->date) : now();
        $dateString = $date->toDateString();

        // その日の勤怠を全ユーザー分取得
        $times = Time::with(['user', 'breakTimes'])
            ->where('date', $dateString)
            ->get();

        $attendances = [];

        // 各勤怠データを整形
        foreach ($times as $time) {
            $start = $time->start_time ? Carbon::parse($time->start_time) : null;
            $end = $time->end_time ? Carbon::parse($time->end_time) : null;

            $totalBreakMinutes = $time->breakTimes->reduce(function ($carry, $break) {
                if ($break->start_time && $break->end_time) {
                    $carry += Carbon::parse($break->end_time)->diffInMinutes(Carbon::parse($break->start_time));
                }
                return $carry;
            }, 0);

            $totalWork = ($start && $end) ? sprintf(
                '%02d:%02d',
                intdiv($end->diffInMinutes($start) - $totalBreakMinutes, 60),
                ($end->diffInMinutes($start) - $totalBreakMinutes) % 60
            ) : '';

            // 勤怠情報を配列に追加
            $attendances[] = [
                'user_name' => $time->user->name,
                'start' => $start ? $start->format('H:i') : '',
                'end' => $end ? $end->format('H:i') : '',
                'break' => $totalBreakMinutes ? sprintf('%02d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60) : '',
                'total' => $totalWork,
                'time_id' => $time->id,
            ];
        }

        // 日付のタイトルを作成
        $title = $date->format('Y年m月d日の勤怠');

        // ビューに渡す
        return view('admin.attendance.index', compact('attendances', 'date', 'title'));
    }


    public function AttendanceApprove(AttendanceRequest $request){
        $user = Auth::user();
        $time_id = $request->input('time_id');
        $time = Time::findOrFail($time_id);
        $date = Carbon::parse($time->date)->format('Y-m-d');

        $input_start = Carbon::parse("{$date} {$request->input('work_start')}:00");
        $input_end = Carbon::parse("{$date} {$request->input('work_end')}:00");

        if ($time->start_time != $input_start) {
            $time->start_time = $input_start;
        }
        if ($time->end_time != $input_end) {
            $time->end_time = $input_end;
        }
        $time->save();

        // 休憩時間の更新
        $breaks = $request->input('breaks', []);
        foreach ($breaks as $id => $breakInput) {
            $break = BreakTime::find($id);
            if ($break && $break->time_id == $time_id) {
                $break_start = Carbon::parse("{$date} {$breakInput['start_time']}:00");
                $break_end = Carbon::parse("{$date} {$breakInput['end_time']}:00");

                if ($break->start_time != $break_start) {
                    $break->start_time = $break_start;
                }
                if ($break->end_time != $break_end) {
                    $break->end_time = $break_end;
                }
                $break->save();
            }
        }

        // 休憩時間の新規作成があった場合
        if (
            isset($breaks['new']['start_time']) && isset($breaks['new']['end_time']) &&
            $breaks['new']['start_time'] !== '' && $breaks['new']['end_time'] !== ''
        ) {
            $newStart = Carbon::parse("{$date} {$breaks['new']['start_time']}:00");
            $newEnd = Carbon::parse("{$date} {$breaks['new']['end_time']}:00");

            BreakTime::create([
                'time_id' => $time_id,
                'start_time' => $newStart,
                'end_time' => $newEnd,
            ]);
        }

        // Application 登録 (管理者用)
        $application = Application::where('time_id', $time_id)->first();

        if ($application) {
            // 既存のApplicationがあれば、dayとapplication_typeを更新
            $application->day = now()->toDateString(); // 現在の日付を保存
            $application->application_type = $request->input('detail'); // 備考欄
            $application->save();
        } else {
            // Applicationがなければ新規作成
            Application::create([
                'time_id' => $time_id,
                'applicant_id' => $user->id,
                'approver_id' => $user->id,
                'day' => now()->toDateString(), // 現在の日付を保存
                'application_type' => $request->input('detail'),
                'status' => 2, // 承認済み
                'approval_date' => now()->toDateString(), // 承認日
            ]);
        }

        return redirect()->route('attendance.show', ['time_id' => $time_id]);
    }

    public function StaffList()
    {
        $staffs = User::where('role', 1)->get();
        return view('admin.staff.index', compact('staffs'));
    }

    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $times = Time::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy('date');

        $attendances = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $ymd = $date->format('Y-m-d');
            $time = $times->get($ymd);

            $breakMinutes = 0;
            if ($time && $time->breakTimes) {
                foreach ($time->breakTimes as $break) {
                    if ($break->start_time && $break->end_time) {
                        $start = Carbon::parse($break->start_time);
                        $end = Carbon::parse($break->end_time);
                        $breakMinutes += $end->diffInMinutes($start);
                    }
                }
            }

            $total = '';
            if ($time && $time->start_time && $time->end_time) {
                $start = Carbon::parse($time->start_time);
                $end = Carbon::parse($time->end_time);
                $worked = $end->diffInMinutes($start) - $breakMinutes;
                $total = floor($worked / 60) . ':' . str_pad($worked % 60, 2, '0', STR_PAD_LEFT);
            }

            $attendances[] = [
                'date' => $date->format('m/d') . '（' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . '）',
                'ymd' => $ymd,
                'start' => optional($time)->start_time ? Carbon::parse($time->start_time)->format('H:i') : '',
                'end' => optional($time)->end_time ? Carbon::parse($time->end_time)->format('H:i') : '',
                'break' => floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT),
                'total' => $total,
                'time_id' => optional($time)->id,
            ];
        }

        return view('admin.staff.show', compact('user', 'year', 'month', 'attendances'));
    }

    public function exportCsv($id, Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        $user = User::findOrFail($id);

        $times = Time::with('breakTimes')
            ->where('user_id', $id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvData = [];

        foreach ($times as $time) {
            // 出勤・退勤整形
            $start = $time->start_time ? Carbon::parse($time->start_time) : null;
            $end = $time->end_time ? Carbon::parse($time->end_time) : null;

            // 休憩時間計算
            $breakMinutes = 0;
            foreach ($time->breakTimes as $break) {
                if ($break->start_time && $break->end_time) {
                    $startBreak = Carbon::parse($break->start_time);
                    $endBreak = Carbon::parse($break->end_time);
                    $breakMinutes += $endBreak->diffInMinutes($startBreak);
                }
            }

            // 合計勤務時間（休憩を引く）
            $worked = '';
            if ($start && $end) {
                $workedMinutes = $end->diffInMinutes($start) - $breakMinutes;
                $worked = floor($workedMinutes / 60) . ':' . str_pad($workedMinutes % 60, 2, '0', STR_PAD_LEFT);
            }

            $csvData[] = [
                Carbon::parse($time->date)->format('Y-m-d'),
                $start ? $start->format('H:i') : '',
                $end ? $end->format('H:i') : '',
                floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT),
                $worked,
            ];
        }

        $filename = "{$user->name}_{$year}_{$month}_勤怠.csv";
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $csvHeader);
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);

        return Response::stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ]);
    }

    public function handleApplicant(Request $request)
    {
        $user = Auth::user();
        if ($user->role != 2) {
            abort(403, 'Unauthorized');
        }

        $status = $request->input('status', 1);
        return redirect()->route('stamp.request.list', ['status' => $status]);
    }

    // 承認画面表示
    public function showApprovePage(Application $attendance_correct_request)
    {
        $time = $attendance_correct_request->time;
        $application = $attendance_correct_request;
        return view('admin.stamp_correction_request.approve', compact('attendance_correct_request', 'time', 'application'));
    }

    // 承認処理
    public function approveApplication(Request $request, Application $attendance_correct_request)
    {
        $attendance_correct_request->status = 2;
        $attendance_correct_request->approver_id = auth()->id();
        $attendance_correct_request->save();

        return redirect()->route('admin.approveApplication.show', ['attendance_correct_request' => $attendance_correct_request->id]);
    }
}