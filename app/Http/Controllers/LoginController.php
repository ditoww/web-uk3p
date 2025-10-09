<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi: satu field "login" untuk email/username
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'login.required' => 'Mohon isi email atau username Anda.',
            'password.required' => 'Mohon isi password Anda.'
        ]);

        $this->ensureIsNotRateLimited($request);

        $login = $request->input('login');
        $password = $request->input('password');

        // Tentukan apakah input adalah email atau username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cek status user terhapus
        $user = User::where($field, $login)->first();
        if ($user && $user->isDeleted !== 0) {
            return back()->withErrors([$field => ucfirst($field).' tersebut telah dihapus.']);
        }

        // Coba autentikasi
        if (Auth::attempt([$field => $login, 'password' => $password])) {
            $request->session()->regenerate();

            Auth::user()->update(['status' => 'active']);

            RateLimiter::clear($this->throttleKey($request));

            switch (Auth::user()->privLevel) {
                case 'spv':
                    return redirect()->route('SPV.spv');
                case 'developer':
                    return redirect()->route('Developer.developer');
                case 'support':
                    return redirect()->route('Support.support');
                default:
                    return redirect('/')->withErrors(['Unauthorized role']);
            }
        }

        // Rate limit: 15 menit (900 detik)
        RateLimiter::hit($this->throttleKey($request), 900);

        return back()->withErrors([
            'login' => 'Email/username atau password Anda salah.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        if (Auth::user()) Auth::user()->update(['status' => 'Away']);
        Auth::logout();        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    protected function ensureIsNotRateLimited(Request $request)
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'login' => __('Terlalu banyak percobaan login. Coba lagi dalam :seconds detik.', [
                'seconds' => RateLimiter::availableIn($this->throttleKey($request)),
            ]),
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('login')).'|'.$request->ip();
    }
}
