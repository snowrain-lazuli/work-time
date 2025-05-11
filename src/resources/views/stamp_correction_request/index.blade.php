@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/index.css') }}">
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
<div class="application-container">
    <div class="application-wrapper">
        <div class="application-list-title">
            <p>申請一覧</p>
        </div>

        {{-- 承認状態の切替ボタン --}}
        <div class="application-status-toggle">
            @if (Auth::user()->role == 2)
            <form class="status-toggle-form" method="POST" action="{{ route('admin.attendance.applicant') }}">
                @csrf
                <input type="hidden" name="status" value="1">
                <button type="submit" {{ $status == 1 ? 'disabled' : '' }}>承認待ち</button>
            </form>

            <form class="status-toggle-form" method="POST" action="{{ route('admin.attendance.applicant') }}">
                @csrf
                <input type="hidden" name="status" value="2">
                <button type="submit" {{ $status == 2 ? 'disabled' : '' }}>承認済み</button>
            </form>
            @else
            <form class="status-toggle-form" method="POST" action="{{ route('user.attendance.applicant') }}">
                @csrf
                <input type="hidden" name="status" value="1">
                <button type="submit" {{ $status == 1 ? 'disabled' : '' }}>承認待ち</button>
            </form>

            <form class="status-toggle-form" method="POST" action="{{ route('user.attendance.applicant') }}">
                @csrf
                <input type="hidden" name="status" value="2">
                <button type="submit" {{ $status == 2 ? 'disabled' : '' }}>承認済み</button>
            </form>
            @endif
        </div>

        {{-- 申請データ一覧テーブル --}}
        <div class="application-table-wrapper">
            <table class="application-table">
                <thead>
                    <tr class="application-table__row">
                        <th class="application-table__header">状態</th>
                        <th class="application-table__header">名前</th>
                        <th class="application-table__header">対象日時</th>
                        <th class="application-table__header">申請理由</th>
                        <th class="application-table__header">申請日</th>
                        <th class="application-table__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                    <tr class="application-table__row">
                        <td class="application-table__cell">
                            {{ $application->status == 1 ? '承認待ち' : '承認完了' }}
                        </td>
                        <td class="application-table__cell">{{ $application->applicant->name }}</td>
                        <td class="application-table__cell">{{ $application->target_date }}</td>
                        <td class="application-table__cell">{{ $application->application_type }}</td>
                        <td class="application-table__cell">{{ $application->application_date }}</td>
                        <td class="application-table__cell">
                            @if (Auth::user()->role == 2)
                            <form
                                action="{{ route('admin.approveApplication', ['attendance_correct_request' => $application->id]) }}"
                                method="get">
                                <button type="submit">詳細</button>
                            </form>
                            @else
                            <form action="{{ route('attendance.show', ['time_id' => $application->time->id]) }}"
                                method="get">
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