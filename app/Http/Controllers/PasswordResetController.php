<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function index()
    {
        return view('auth.forgotpass');
    }

    public function sendData(Request $request) {
        $emailCheck = User::where('email', $request->email)->first();

        $user = User::where('username', $request->email)->first();
    }

    public function sendEmail(Request $request)
    {
        $request->validate(['email' => 'required|string|max:191'], [
            'email.required' => "Mohon isi email atau username Anda.",
            'email.string' => "Mohon isi email atau username yang valid.",
            'email.max' => "Email atau username tersebut harus memiliki 191 hruf atau ke bawah."
        ]);

        $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Check if user exists
        $user = User::where($field, $request->email)->where('isDeleted', 0)->first();

        if (!$user) {
            $message = $field === 'email'
                ? 'Email tidak ditemukan.'
                : 'Username tidak ditemukan.';
            return back()->withErrors(['email' => $message]);
        }

        if (!empty($user->email)) {
            $token = Password::broker('users')->createToken($user);

            // Send the custom email
            Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email, $user->name));

            return back()->with('status', 'Link reset password telah dikirimkan ke email Anda.');
        } else {
            $user->isRequestingPassword = 1;
            $user->save();

            return back()->with('status', 'Password akan dikirimkan ke Super Admin.');
        }
    }
}
