<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Actions\Fortify\CreateNewUser;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
    }

    public function boot(): void
    {
        // 一般ユーザーログインビュー
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 一般ユーザー登録ビュー
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // Fortifyの認証ロジック（ユーザーを返すだけ）
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                return Auth::user();
            }

            return null;
        });

        // レート制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input(Fortify::username());
            return Limit::perMinute(5)->by(Str::lower($email) . '|' . $request->ip());
        });
    }
}