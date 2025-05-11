<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\ContentsController;
use App\Http\Controllers\AdminContentsController;
use App\Http\Controllers\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// 一般ユーザー
Route::get('/', function () {
    return redirect('/login');
});
Route::view('/login', 'auth.login');
Route::view('/register', 'auth.register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::post('/login', [AuthenticatedSessionController::class, 'login'])->name('login');

// 管理者用
Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'auth.admin_login')->name('login_form');
    Route::post('/login', [AuthenticatedSessionController::class, 'adminLogin'])->name('login');
});

// ログアウト（共通）
Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
//勤怠登録(一般)
Route::get('/attendance', [ContentsController::class, 'attendance'])->name('attendance.create');
Route::post('/attendance', [ContentsController::class, 'attendance'])->name('attendance.create');
Route::post('/attendance/start', [ContentsController::class, 'start'])->name('start');
Route::post('/attendance/break', [ContentsController::class, 'break'])->name('break');
Route::post('/attendance/break/end', [ContentsController::class, 'break_end'])->name('break_end');
Route::post('/attendance/end', [ContentsController::class, 'end'])->name('end');

//勤怠管理(一般)
Route::get('/attendance/list', [ContentsController::class, 'attendanceList'])->name('attendance.index');
Route::get('/attendance/previous', function (Request $request) {
    $year = $request->year;
    $month = $request->month - 1;
    if ($month < 1) {
        $month = 12;
        $year--;
    }
    return redirect()->route('attendance.index', ['year' => $year, 'month' => $month]);
})->name('attendance.previous');
Route::get('/attendance/next', function (Request $request) {
    $year = $request->year;
    $month = $request->month + 1;
    if ($month > 12) {
        $month = 1;
        $year++;
    }
    return redirect()->route('attendance.index', ['year' => $year, 'month' => $month]);
})->name('attendance.next');
Route::get('/attendance/select', function (Request $request) {
    [$year, $month] = explode('-', $request->selected_month);
    return redirect()->route('attendance.index', ['year' => $year, 'month' => $month]);
})->name('attendance.select');

//勤怠詳細(一般)
Route::get('/attendance/{time_id}', [ContentsController::class, 'show'])->name('attendance.show');
Route::post('/attendance/{time_id}', [ContentsController::class, 'update'])->name('attendance.update');

Route::match(['get', 'post'], '/user/request/list', [ContentsController::class, 'handleApplicant'])->name('user.attendance.applicant');

Route::get('/stamp_correction_request/list', [ContentsController::class, 'showList'])->name('stamp.request.list');

});


Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    // 管理者勤怠一覧（デフォルトは本日）
    Route::get('/admin/attendance/list', [AdminContentsController::class, 'AdminAttendanceList'])
        ->name('admin.attendance.index');

    // 日別ナビゲーション（前日・翌日・日付指定）
    Route::get('/admin/attendance/previous', function (Request $request) {
        $date = Carbon::parse($request->date)->subDay()->toDateString();
        return redirect()->route('admin.attendance.index', ['date' => $date]);
    })->name('admin.attendance.previous');

    Route::get('/admin/attendance/next', function (Request $request) {
        $date = Carbon::parse($request->date)->addDay()->toDateString();
        return redirect()->route('admin.attendance.index', ['date' => $date]);
    })->name('admin.attendance.next');

    Route::get('/admin/attendance/select', function (Request $request) {
        return redirect()->route('admin.attendance.index', ['date' => $request->selected_day]);
    })->name('admin.attendance.select');

    // スタッフ別の勤怠詳細
    Route::get('/admin/staff/list', [AdminContentsController::class, 'StaffList'])
        ->name('admin.staff.list');

    // スタッフ別勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [AdminContentsController::class, 'show'])
        ->name('admin.staff.show');

    // 申請一覧
    Route::match(['get', 'post'], '/admin/request/list', [AdminContentsController::class, 'handleApplicant'])->name('admin.attendance.applicant');

    // 修正処理
    Route::post('/admin/attendance/approve', [AdminContentsController::class, 'AttendanceApprove'])
        ->name('attendance.approve');

    // 承認処理
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminContentsController::class, 'showApprovePage'])
        ->name('admin.approveApplication.show');

    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminContentsController::class, 'approveApplication'])
        ->name('admin.approveApplication');

    // 月別ナビゲーション（前月・翌月・月指定）
    Route::get('/admin/attendance/staff/{id}/previous', function ($id, Request $request) {
        $date = Carbon::createFromDate($request->year, $request->month, 1)->subMonth();
        return redirect()->route('admin.staff.show', [
            'id' => $id,
            'year' => $date->year,
            'month' => $date->month,
        ]);
    })->name('admin.staff.previous');

    Route::get('/admin/attendance/staff/{id}/next', function ($id, Request $request) {
        $date = Carbon::createFromDate($request->year, $request->month, 1)->addMonth();
        return redirect()->route('admin.staff.show', [
            'id' => $id,
            'year' => $date->year,
            'month' => $date->month,
        ]);
    })->name('admin.staff.next');

    Route::get('/admin/attendance/staff/{id}/select', function ($id, Request $request) {
        [$year, $month] = explode('-', $request->selected_month);
        return redirect()->route('admin.staff.show', [
            'id' => $id,
            'year' => (int) $year,
            'month' => (int) $month,
        ]);
    })->name('admin.staff.select');

    // CSVエクスポート
    Route::get('/admin/attendance/staff/{id}/export', [AdminContentsController::class, 'exportCsv'])
        ->name('admin.staff.exportCsv');
});