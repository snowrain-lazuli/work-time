@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('link')
<div class="header__link">
    @if (Auth::user()->role != 2)
    <form action="{{ route('attendance.create') }}" method="get">
        <input type="submit" value="勤怠">
    </form>
    <form action="{{ route('attendance.index') }}" method="get">
        <input type="submit" value="勤怠一覧">
    </form>
    <form action="{{ route('user.attendance.applicant') }}" method="get">
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
<div class="attendance-container">
    <div class="attendance-wrapper">
        <div class="index-title">
            <p>勤怠一覧</p>
        </div>

        <div class="date-navigation">
            <form class="form-date-navigation previous-month" action="{{ route('attendance.previous') }}" method="get">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit">
                    <img src="{{ asset('images/arrow.png') }}" alt="←" class="arrow previous-arrow">前月
                </button>
            </form>

            <div class="month-center-group">
                <div class="calendar-wrapper">
                    <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" class="calendar-icon">
                    <form id="admin-date-form" action="{{ route('attendance.select') }}" method="get">
                        <input type="month" id="admin-date-picker" name="selected_month"
                            value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}"
                            onchange="this.form.submit()" />
                    </form>
                </div>
                <div class="current-month-display">
                    {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
                </div>
            </div>


            <form class="form-date-navigation next-month" action="{{ route('attendance.next') }}" method="get">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit">
                    翌月
                    <img src="{{ asset('images/arrow.png') }}" alt="→" class="arrow next-arrow">
                </button>
            </form>
        </div>

        <div class="work-list">
            <table class="work-list-table">
                <thead>
                    <tr>
                        <th>日付</th>
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
                        <td>{{ $attendance['date'] }}</td>
                        <td>{{ $attendance['start'] }}</td>
                        <td>{{ $attendance['end'] }}</td>
                        <td>{{ $attendance['break'] }}</td>
                        <td>{{ $attendance['total'] }}</td>
                        <td>
                            @if (!empty($attendance['time_id']))
                            <form action="{{ route('attendance.show', ['time_id' => $attendance['time_id']]) }}"
                                method="get">
                                <input type="hidden" name="date" value="{{ $attendance['ymd'] }}">
                                <button type="submit">詳細</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection