@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('link')
<div class="header__link">
    @if (Auth::user()->role == 2)
    <form action="{{ route('admin.attendance.index') }}" method="get">
        <input type="submit" value="勤怠一覧">
    </form>
    <form action="{{ route('admin.staff.list') }}" method="get">
        <input type="submit" value="スタッフ一覧">
    </form>
    <form action="{{ route('admin.attendance.applicant') }}" method="get">
        <input type="submit" value="申請一覧">
    </form>
    @else
    <form action="{{ route('attendance.create') }}" method="get">
        <input type="submit" value="勤怠">
    </form>
    <form action="{{ route('attendance.index') }}" method="get">
        <input type="submit" value="勤怠一覧">
    </form>
    <form action="{{ route('user.attendance.applicant') }}" method="get">
        <input type="submit" value="申請一覧">
    </form>
    @endif

    <form action="{{ route('logout') }}" method="post">
        @csrf
        <input type="submit" value="ログアウト">
    </form>
</div>
@endsection

@section('content')
<div class="attendance-detail-wrapper">
    <div class="index-title">
        <p>勤怠詳細</p>
    </div>

    <div class="attendance-detail-container">
        <form action="{{ $formAction }}" method="POST">
            @csrf

            <table class="attendance-detail-table">
                <tr class="attendance-detail-row">
                    <th class="attendance-detail-header">名前</th>
                    <td colspan="3">{{ $time->user->name }}</td>
                </tr>
                <tr class="attendance-detail-row">
                    <th class="attendance-detail-header">日付</th>
                    <td colspan="2">{{ \Carbon\Carbon::parse($time->date)->format('Y年') }}</td>
                    <td>{{ \Carbon\Carbon::parse($time->date)->format('n月j日') }}</td>
                </tr>
                <tr class="attendance-detail-row">
                    <th class="attendance-detail-header">出勤・退勤</th>
                    <td>
                        <input type="text" name="work_start" value="{{ old('work_start', $time->formatted_start) }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                    <td>〜</td>
                    <td>
                        <input type="text" name="work_end" value="{{ old('work_end', $time->formatted_end) }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                </tr>

                @foreach ($time->breakTimes as $index => $break)
                <tr class="attendance-detail-row">
                    <th class="attendance-detail-header">休憩{{ $index + 1 }}</th>
                    <td>
                        <input type="text" name="breaks[{{ $break->id }}][start_time]"
                            value="{{ old('breaks.' . $break->id . '.start_time', $break->formatted_start) }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                    <td>〜</td>
                    <td>
                        <input type="text" name="breaks[{{ $break->id }}][end_time]"
                            value="{{ old('breaks.' . $break->id . '.end_time', $break->formatted_end) }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                </tr>
                @endforeach

                <tr class="attendance-detail-row">
                    <th class="attendance-detail-header">休憩{{ $time->breakTimes->count() + 1 }}</th>
                    <td>
                        <input type="text" name="breaks[new][start_time]" value="{{ old('breaks.new.start_time') }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                    <td>〜</td>
                    <td>
                        <input type="text" name="breaks[new][end_time]" value="{{ old('breaks.new.end_time') }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                </tr>

                <tr class="attendance-detail-row">
                    <th class="attendance-detail-header">備考</th>
                    <td colspan="3">
                        <input type="text" name="detail"
                            value="{{ old('detail', $application->application_type ?? '') }}"
                            {{ (!$isAdmin && $application) ? 'disabled' : '' }}>
                    </td>
                </tr>
            </table>

            @if ($errors->any())
            <div class="error-summary">
                @foreach ($errors->all() as $error)
                <div class="error-message">{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <div class="attendance-detail-action">
                @if ($isAdmin)
                <input type="hidden" name="time_id" value="{{ $time->id }}">
                <button type="submit" class="edit-button">修正</button>
                @else
                @if (!$application)
                <button type="submit" class="edit-button">修正</button>
                @elseif ($application->status == 1)
                <p class="attendance-detail-note">・承認待ちの為修正はできません</p>
                @elseif ($application->status == 2)
                <p class="attendance-detail-note">・承認済みの為修正はできません</p>
                @endif
                @endif
            </div>
        </form>
    </div>
</div>
@endsection