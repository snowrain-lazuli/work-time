<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Time;
use App\Models\BreakTime;
use App\Models\Application;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\Log;

class ContentsController extends Controller
{
    //勤怠登録
    public function attendance()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $time = Time::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$time) {
            $status = 'not_started'; // 出勤前
        } else {
            $status = $time->status;
        }

        $date = now();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[$date->dayOfWeek];

        return view('attendance.create', compact('status', 'date', 'weekday'));
    }

    public function start()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();
        $start_time = Carbon::now()->format('Y-m-d H:i:s');

        Time::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => $start_time,
            'status' => 'working',
            'end_time' => null,
        ]);

        return redirect('/attendance');
    }

    public function break()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();

        // 今日の出勤記録を取得
        $time = Time::where('user_id', $user->id)->where('date', $today)->firstOrFail();

        // 休憩開始時間を登録
        BreakTime::create([
            'time_id'     => $time->id,
            'start_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'end_time'   => null,
        ]);

        // 勤務ステータスをbreakingに変更
        $time->status = 'breaking';
        $time->save();

        return redirect('/attendance');
    }

    public function break_end()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();

        // 今日の出勤記録を取得
        $time = Time::where('user_id', $user->id)->where('date', $today)->firstOrFail();

        // 直近の休憩時間を取得
        $break = BreakTime::where('time_id', $time->id)->orderBy('start_time', 'desc')
            ->first();

        $break->end_time = Carbon::now()->format('Y-m-d H:i:s');
        $break->save();

        // 勤務ステータスをworkingに変更
        $time->status = 'working';
        $time->save();

        return redirect('/attendance');
    }

    public function end()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();
        $end_time = Carbon::now()->format('Y-m-d H:i:s');

        // 今日の出勤記録を取得
        $time = Time::where('user_id', $user->id)->where('date', $today)->firstOrFail();

        // 終了時刻の打刻と勤務ステータスをcompletedに変更
        $time->end_time = $end_time;
        $time->status = 'completed';
        $time->save();

        return redirect('/attendance');
    }

    //勤怠管理(一般)

    public function attendanceList(Request $request)
    {
        $user = Auth::user();

        //表示月関係の処理
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;
        $startOfMonth = Carbon::create($year, $month)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month)->endOfMonth();

        //日付のリスト
        $dates = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dates->push($date->copy());
        }

        $times = Time::where('user_id', $user->id)->whereBetween('date', [$startOfMonth, $endOfMonth])->get()->keyBy(fn($time) => Carbon::parse($time->date)->format('Y-m-d'));

        $attendances = [];

        foreach ($dates as $date) {
            $key = $date->format('Y-m-d');
            $record = $times[$key] ?? null;

            // 日付は必ず表示
            $formatted = [
                'date' => $date->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . ')',
                'start' => '',
                'end' => '',
                'break' => '',
                'total' => '',
                'ymd' => $key,
                'time_id' => null,
            ];

            if ($record && $record->end_time) {
                $start = $record->start_time ? Carbon::parse($record->start_time) : null;
                $end = Carbon::parse($record->end_time);

                if ($start) {
                    $formatted['start'] = $start->format('H:i');
                }

                $formatted['end'] = $end->format('H:i');

                $breaks = BreakTime::where('time_id', $record->id)->get();

                $totalBreakMinutes = 0;
                foreach ($breaks as $break) {
                    if ($break->start_time && $break->end_time) {
                        $totalBreakMinutes += Carbon::parse($break->end_time)
                            ->diffInMinutes(Carbon::parse($break->start_time));
                    }
                }

                $formatted['break'] = sprintf('%02d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60);

                if ($start) {
                    $workMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
                    $formatted['total'] = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
                }

                $formatted['time_id'] = $record->id;
            }

            $attendances[] = $formatted;
        }

        return view('attendance.index', compact('attendances', 'year', 'month'));
    }

    //勤怠詳細(一般)
    public function show($time_id)
    {
        $user = Auth::user();
        $time = Time::with(['user', 'breakTimes'])->findOrFail($time_id);
        $application = Application::where('time_id', $time_id)->first();

        // 管理者かどうかを判定
        $isAdmin = $user->role == 2;

        // フォーム送信先の設定
        if ($isAdmin) {
            $formAction = '/admin/attendance/approve';
        } else {
            $formAction = "/attendance/{$time_id}";
        }

        // 時刻の整形
        $time->formatted_start = $time->start_time ? \Carbon\Carbon::parse($time->start_time)->format('H:i') : '';
        $time->formatted_end = $time->end_time ? \Carbon\Carbon::parse($time->end_time)->format('H:i') : '';

        // 休憩時間の整形
        foreach ($time->breakTimes as $break) {
            $break->formatted_start = $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '';
            $break->formatted_end = $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '';
        }

        return view('attendance.show', compact('time', 'application', 'isAdmin', 'formAction'));
    }

    public function update(AttendanceRequest $request, $time_id)
    {
        $user = Auth::user();

        // 勤務時間の更新
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

        if (
            !empty($breaks['new']['start_time']) &&
            !empty($breaks['new']['end_time'])
        ) {
            $newStart = Carbon::parse("{$date} {$breaks['new']['start_time']}:00");
            $newEnd = Carbon::parse("{$date} {$breaks['new']['end_time']}:00");

            BreakTime::create([
                'time_id' => $time_id,
                'start_time' => $newStart,
                'end_time' => $newEnd,
            ]);
        }

        // Application 登録
        Application::create([
            'time_id' => $time->id,
            'applicant_id' => $user->id,
            'approver_id' => null,
            'day' => now()->toDateString(), // 現在の日付を保存
            'application_type' => $request->input('detail'), // 備考欄
            'status' => 1,
        ]);

        return redirect()->route('attendance.show', ['time_id' => $time_id]);
    }


    public function handleApplicant(Request $request)
    {
        $status = $request->input('status', 1);
        return redirect()->route('stamp.request.list', ['status' => $status]);
    }

    public function showList(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 1);

        if ($user->role == 2) {
            // 管理者：承認待ち → approver_idがnull、承認済み → approver_idが自分
            if ($status == 1) {
                $applications = Application::with(['time', 'applicant'])
                    ->whereNull('approver_id')
                    ->get()->map(function ($app) {
        $app->application_date = Carbon::parse($app->day)->format('Y/m/d');
        $app->target_date = $app->time ? Carbon::parse($app->time->date)->format('Y/m/d') : null;
        return $app;
    });
            } elseif ($status == 2) {
                $applications = Application::with(['time', 'applicant'])
                    ->where('approver_id', $user->id)
                    ->get()->map(function ($app) {
        $app->application_date = Carbon::parse($app->day)->format('Y/m/d');
        $app->target_date = $app->time ? Carbon::parse($app->time->date)->format('Y/m/d') : null;
        return $app;
    });
            }
        } else {
            // 一般ユーザー：自分の申請のみ
            $applications = Application::with(['time', 'applicant'])
                ->where('applicant_id', $user->id)
                ->where('status', $status)
                ->get()->map(function ($app) {
        $app->application_date = Carbon::parse($app->day)->format('Y/m/d');
        $app->target_date = $app->time ? Carbon::parse($app->time->date)->format('Y/m/d') : null;
        return $app;
    });
        }

        return view('stamp_correction_request.index', compact('applications', 'status'));
    }

}