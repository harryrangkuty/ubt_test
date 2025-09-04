<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function user()
    {
        return request()->user();
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $logoutLink = config('services.sidara.url') . '/sso/logout?redirect_uri=' . urlencode(url('/'));

        return redirect()->away($logoutLink);
    }
}
