@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('link')
<div class="header__link">
    @if (isset($status) && $status === 'completed')
    <form action="{{ route('attendance.index') }}" method="get">
        @csrf
        <input type="submit" value="今月の出勤一覧">
    </form>
    @else
    <form action="{{ route('attendance.create') }}" method="get">
        <input type="submit" value="勤怠">
    </form>
    <form action="{{ route('attendance.index') }}" method="get">
        <input type="submit" value="勤怠一覧">
    </form>
    @endif

    <form action="{{ route('user.attendance.applicant') }}" method="get">
        <input type="submit" value="申請一覧">
    </form>

    <form action="{{ route('logout') }}" method="post">
        @csrf
        <input type="submit" value="ログアウト">
    </form>
</div>
@endsection

@section('content')
<div class="attendance-panel">
    <div class="attendance-panel__status">
        <span class="attendance-panel__status-label">
            @switch($status)
            @case('working') 勤務中 @break
            @case('breaking') 休憩中 @break
            @case('completed') 退勤済み @break
            @default 勤務外
            @endswitch
        </span>
    </div>

    <div class="attendance-panel__date-display">
        <span class="attendance-date">{{ $date->format('Y年n月j日') }}（{{ $weekday }}）</span>
        <span class="attendance-time">{{ $date->format('H:i') }}</span>
    </div>

    <div class="attendance-panel__action-buttons">
        @if ($status === 'not_started')
        <form method="POST" action="{{ route('start') }}">
            @csrf
            <button class="attendance-button btn btn-start">出勤</button>
        </form>
        @elseif ($status === 'working')
        <form method="POST" action="{{ route('end') }}">
            @csrf
            <button class="attendance-button btn btn-end">退勤</button>
        </form>
        <form method="POST" action="{{ route('break') }}">
            @csrf
            <button class="attendance-button btn btn-break-start">休憩入</button>
        </form>
        @elseif ($status === 'breaking')
        <form method="POST" action="{{ route('break_end') }}">
            @csrf
            <button class="attendance-button btn btn-break-end">休憩戻</button>
        </form>
        @elseif ($status === 'completed')
        <div class="attendance-panel__leaving-comment">お疲れ様でした。</div>
        @endif
    </div>
</div>
@endsection