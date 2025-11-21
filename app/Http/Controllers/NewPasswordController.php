<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class NewPasswordController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->route()->parameter('token');
        $email = $request->email;

        $record = DB::table('user_password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return redirect('/');
        }

        if (!Hash::check($token, $record->token)) {
            return redirect('/');
        }

        if (now()->subMinutes(config('auth.passwords.users.expire'))->gt($record->created_at)) {
            return redirect('/');
        }

        return view('auth.changepass', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'token' => 'required|max:191',
            'email' => 'required|email',
            'password' => 'required|max:191|confirmed'
        ], [
            'password.required' => 'Mohon isi password baru Anda.',
            'password.max' => 'Password harus dibawah 192 huruf.',
            'password.confirmed' => 'Kedua password tersebut tidak sama.'
        ]);

        $resetter = DB::table('user_password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetter || !Hash::check($request->token, $resetter->token)) {
            return back()->withErrors(['token' => 'Token tidak valid atau sudah kedaluwarsa.']);
        }

        $akun = User::where('email', $request->email)->where('isDeleted', 0)->first();
        if (!$akun) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $akun->password = Hash::make($request->password);
        $akun->save();

        DB::table('user_password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password berhasil diubah.');
    
    }
}
