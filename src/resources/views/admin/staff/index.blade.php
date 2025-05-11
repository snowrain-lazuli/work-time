@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
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
<div class="staff-container">
    <div class="staff-wrapper">
        <div class="staff-index-title">
            <p>スタッフ一覧</p>
        </div>

        <div class="staff-table-wrapper">
            <table class="staff-table">
                <thead>
                    <tr class="staff-table__row">
                        <th class="staff-table__header">名前</th>
                        <th class="staff-table__header">メールアドレス</th>
                        <th class="staff-table__header">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffs as $staff)
                    <tr class="staff-table__row">
                        <td class="staff-table__cell">{{ $staff->name }}</td>
                        <td class="staff-table__cell">{{ $staff->email }}</td>
                        <td class="staff-table__cell">
                            <form action="{{ route('admin.staff.show', ['id' => $staff->id]) }}" method="get">
                                <button type="submit" class="btn-detail">詳細</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection