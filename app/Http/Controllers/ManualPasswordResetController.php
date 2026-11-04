<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ManualPasswordResetController extends Controller //Made in case of user needs passwords without using email.
{
    public function index()
    {
        $requestings = User::where('isRequestingPassword', 1)->where('isDeleted', 0)->get();
        return view('Admin.password', ['alldata' => $requestings]);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'pass' => 'required|confirmed|max:191',
        ];
        
        $messages = [
            'pass.required' => 'Mohon isi password baru.',
            'pass.max' => 'Password harus dibawah 192 huruf.',
            'pass.confirmed' => 'Password tersebut tidak sama.',
        ];

        $validator = Validator::make($request->only(['pass', 'pass_confirmation']), $rules, $messages);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput()->with('error', "Terdapat sebuah masalah dalam penggantian password. Klik tombol Tambah Password untuk mengetahui masalahnya.");

        $akun = User::findOrFail($id);

        $akun->password = Hash::make($request->pass);
        $akun->isRequestingPassword = 0;
        $akun->save();

        return redirect()->back()->with('success', 'Password berhasil diupdate!');
    }

    public function validateUpdate(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $rules = [
            'pass' => 'required|confirmed|max:191',
        ];
        
        $messages = [
            'pass.required' => 'Mohon isi password baru.',
            'pass.confirmed' => 'Password tersebut tidak sama.',
            'pass.max' => 'Password harus dibawah 192 huruf.',
        ];

        $validator = Validator::make($request->only(['pass', 'pass_confirmation']), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
