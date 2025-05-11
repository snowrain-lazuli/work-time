<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    // 一般ログイン
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['role'] = 1; // 一般ユーザーのみ

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('attendance.create'); // 一般ユーザ画面へ
        }

        return back()->withErrors([
            'email' => '一般ユーザーのみログイン可能です。',
        ]);
    }

    // 管理者ログイン
    public function adminLogin(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['role'] = 2; // 管理者のみ

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.index'); // 管理者画面へ
        }

        return back()->withErrors([
            'email' => '管理者のみログイン可能です。',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}