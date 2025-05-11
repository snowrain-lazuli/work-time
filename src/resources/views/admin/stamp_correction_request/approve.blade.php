@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/approve.css') }}">
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
<div class="content-inner">
    <div class="detail-title-wrapper">
        <p class="detail-title">勤怠詳細</p>
    </div>

    <div class="work-list">
        <form
            action="{{ route('admin.approveApplication', ['attendance_correct_request' => $attendance_correct_request->id]) }}"
            method="POST">
            @csrf
            <div class="table-wrapper">
                <table class="work-list-table styled-table">
                    <tr class="work-list-table__row">
                        <th class="work-list-table__header align-left">名前</th>
                        <td colspan="3">{{ $time->user->name }}</td>
                    </tr>
                    <tr class="work-list-table__row">
                        <th class="work-list-table__header align-left">日付</th>
                        <td colspan="2" class="align-left">{{ \Carbon\Carbon::parse($time->date)->format('Y年') }}</td>
                        <td class="align-left">{{ \Carbon\Carbon::parse($time->date)->format('m月d日') }}</td>
                    </tr>
                    <tr class="work-list-table__row">
                        <th class="work-list-table__header align-left">出勤・退勤</th>
                        <td>{{ $time->start_time ? \Carbon\Carbon::parse($time->start_time)->format('H:i') : '未設定' }}
                        </td>
                        <td>〜</td>
                        <td>{{ $time->end_time ? \Carbon\Carbon::parse($time->end_time)->format('H:i') : '未設定' }}</td>
                    </tr>

                    @foreach ($time->breakTimes as $index => $break)
                    <tr class="work-list-table__row">
                        <th class="work-list-table__header align-left">休憩{{ $index + 1 }}</th>
                        <td>{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '未設定' }}
                        </td>
                        <td>〜</td>
                        <td>{{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '未設定' }}</td>
                    </tr>
                    @endforeach

                    <tr class="work-list-table__row">
                        <th class="work-list-table__header align-left">休憩{{ $index + 2 }}</th>
                        <td colspan="3"></td>
                    </tr>

                    <tr class="work-list-table__row">
                        <th class="work-list-table__header align-left">備考</th>
                        <td colspan="3" class="align-left">{{ $application->application_type ?? '' }}</td>
                    </tr>
                </table>

                <div class="approval-button-area">
                    @if ($attendance_correct_request->status == 2)
                    <span class="approved-label">承認済み</span>
                    @else
                    <button type="submit" class="approval-button">承認</button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection