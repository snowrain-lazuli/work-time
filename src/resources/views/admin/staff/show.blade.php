@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/show.css') }}">
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
    <div class="index-title">
        <p>{{ $user->name }}さんの勤怠一覧</p>
    </div>

    <!-- 月切り替え -->
    <div class="date-navigation">
        <form class="previous-month-navigation" action="{{ route('admin.staff.previous', ['id' => $user->id]) }}"
            method="get">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit">
                <img src="{{ asset('images/arrow.png') }}" class="arrow-left" alt="前月">
                前月
            </button>
        </form>

        <div class="month-center-group">
            <form id="admin-month-form" action="{{ route('admin.staff.select', ['id' => $user->id]) }}" method="get"
                class="month-picker-form calendar-label">
                <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" class="calendar-icon">
                <input type="month" id="admin-month-picker" name="selected_month"
                    value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" class="calendar-only"
                    title="月を選択" />
            </form>
            <div class="current-month-display">{{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</div>
        </div>

        <form class="next-month-navigation" action="{{ route('admin.staff.next', ['id' => $user->id]) }}" method="get">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit">
                翌月
                <img src="{{ asset('images/arrow.png') }}" class="arrow-right" alt="翌月">
            </button>
        </form>
    </div>

    <div class="attendance-list">
        <table class="attendance-list-table">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance['date'] }}</td>
                <td>{{ $attendance['start'] }}</td>
                <td>{{ $attendance['end'] }}</td>
                <td>{{ $attendance['break'] }}</td>
                <td>{{ $attendance['total'] }}</td>
                <td>
                    @if(!empty($attendance['time_id']))
                    <form action="{{ route('attendance.show', ['time_id' => $attendance['time_id']]) }}" method="get">
                        @csrf
                        <input type="hidden" name="date" value="{{ $attendance['ymd'] }}">
                        <button type="submit">詳細</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>

        <!-- CSV出力 -->
        <div class="csv-export">
            <form action="{{ route('admin.staff.exportCsv', ['id' => $user->id]) }}" method="get">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn btn-primary">CSV出力</button>
            </form>
        </div>
    </div>
</div>
@endsection