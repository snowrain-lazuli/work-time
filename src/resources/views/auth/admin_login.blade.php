@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin_login.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="admin-login-form">
    <h2 class="admin-login-form__heading content__heading">管理者ログイン</h2>
    <div class="admin-login-form__inner">
        <form class="admin-login-form__form" action="{{ route('admin.login') }}" method="POST">
            @csrf

            @error('no_data')
            <p class="admin-login-form__error-message">{{ $message }}</p>
            @enderror

            <div class="admin-login-form__group">
                <label class="admin-login-form__label" for="email">メールアドレス</label>
                <input class="admin-login-form__input" type="text" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                <p class="admin-login-form__error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-login-form__group">
                <label class="admin-login-form__label" for="password">パスワード</label>
                <input class="admin-login-form__input" type="password" name="password" id="password">
                @error('password')
                <p class="admin-login-form__error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-login-form__actions">
                <input class="admin-login-form__btn btn" type="submit" value="管理者ログインする">
            </div>
        </form>
    </div>
</div>
@endsection