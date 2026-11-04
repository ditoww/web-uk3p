<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\DevRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller //Made in case of new applications.
{
    public function index()
    {
        $data = Application::where('isDeleted', 0)->get();
        $roles = DevRole::where('isDeleted', 0)->get();
        return view('Admin.application', ['apps' => $data, 'roles' => $roles]);
    }

    public function store(Request $request)
    {
        $rules = [
            'appName' => 'required|string|max:100|unique:devRoles,roleName',
            'roleID' => 'required|exists:devRoles,id'
        ];
        $messages = [
            'appName.required' => 'Mohon isi nama app.',
            'appName.max' => 'Nama app harus 100 huruf atau dibawah.',
            'appName.unique' => 'Nama app sudah dipakai.',
            'roleID.required' => 'Mohon pilih tipe aplikasi',
            'roleID.exists' => 'Tipe aplikasi tersebut tidak terdaftarkan.'
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput()->with('error', "Terdapat sebuah masalah dalam penambahan daftar aplikasi. Klik tombol Tambah Aplikasi untuk mengetahui masalahnya.");

        Application::create([
            'appName' => $request->appName,
            'roleID' => $request->roleID
        ]);

        return redirect()->back()->with('success', 'Application berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'appName' => 'required|string|max:100|unique:devRoles,roleName,' . $id . ',id',
            'roleID' => 'required|exists:devRoles,id'
        ];
        
        $messages = [
            'appName.required' => 'Mohon isi nama app.',
            'appName.max' => 'Nama app harus 100 huruf atau dibawah.',
            'appName.unique' => 'Nama app sudah dipakai.',
            'roleID.required' => 'Mohon pilih tipe aplikasi',
            'roleID.exists' => 'Tipe aplikasi tersebut tidak terdaftarkan.'
        ];

         $validator = Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput()->with('error', "Terdapat sebuah masalah dalam perbaruan daftar aplikasi. Klik tombol Tambah Aplikasi untuk mengetahui masalahnya.");

        $application = Application::findOrFail($id);
        $application->update([
            'appName' => $request->appName,
            'roleID' => $request->roleID
        ]);

        return redirect()->back()->with('success', 'Application berhasil diupdate!');
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        $application->isDeleted = 1;
        $application->save();
        return redirect()->back()->with('success', 'Application berhasil dihapus!');
    }

    public function validateStore(Request $request): \Illuminate\Http\JsonResponse
    {
         $rules = [
            'appName' => 'required|string|max:100|unique:devRoles,roleName',
            'roleID' => 'required|exists:devRoles,id'
        ];
        
        $messages = [
            'appName.required' => 'Mohon isi nama app.',
            'appName.max' => 'Nama app harus 100 huruf atau dibawah.',
            'appName.unique' => 'Nama app sudah dipakai.',
            'roleID.required' => 'Mohon pilih tipe aplikasi',
            'roleID.exists' => 'Tipe aplikasi tersebut tidak terdaftarkan.'
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function validateUpdate(Request $request, $id): \Illuminate\Http\JsonResponse
    {
         $rules = [
            'appName' => 'required|string|max:100|unique:devRoles,roleName,' . $id . ',id',
            'roleID' => 'required|exists:devRoles,id'
        ];
        
        $messages = [
            'appName.required' => 'Mohon isi nama app.',
            'appName.max' => 'Nama app harus 100 huruf atau dibawah.',
            'appName.unique' => 'Nama app sudah dipakai.',
            'roleID.required' => 'Mohon pilih tipe aplikasi',
            'roleID.exists' => 'Tipe aplikasi tersebut tidak terdaftarkan.'
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
