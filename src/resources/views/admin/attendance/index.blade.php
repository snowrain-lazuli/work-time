@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
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
    <form action="{{ route('logout') }}" method="post">
        @csrf
        <input type="submit" value="ログアウト">
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="attendance-wrapper">
    <div class="admin-index-title">
        <p>{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠</p>
    </div>

    <div class="admin-date-navigation">
        <form action="{{ route('admin.attendance.previous') }}" method="get">
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="arrow-button">
                <img src="{{ asset('images/arrow.png') }}" alt="前日" class="arrow-left">
                <span>前日</span>
            </button>
        </form>

        <form id="admin-date-form" action="{{ route('admin.attendance.select') }}" method="get">
            <label class="calendar-label">
                <img src="{{ asset('images/calendar.png') }}" alt="calendar" class="calendar-icon">
                <input type="date" id="admin-date-picker" name="selected_day" value="{{ $date }}" class="calendar-only">
            </label>
            <span id="admin-selected-date">
                {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
            </span>
        </form>

        <form action="{{ route('admin.attendance.next') }}" method="get">
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="arrow-button">
                <span>翌日</span>
                <img src="{{ asset('images/arrow.png') }}" alt="翌日" class="arrow-right">
            </button>
        </form>
    </div>

    <div class="admin-work-list">
        <table class="admin-work-list-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance['user_name'] }}</td>
                    <td>{{ $attendance['start'] }}</td>
                    <td>{{ $attendance['end'] }}</td>
                    <td>{{ $attendance['break'] }}</td>
                    <td>{{ $attendance['total'] }}</td>
                    <td>
                        <form action="{{ route('attendance.show', ['time_id' => $attendance['time_id']]) }}"
                            method="get">
                            @csrf
                            <button type="submit">詳細</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection