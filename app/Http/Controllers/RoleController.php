<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DevRole;

class RoleController extends Controller
{
    // Tampilkan semua data dev role
    public function index()
    {
        $data = DevRole::where('isDeleted', 0)->get();
        return view('Role.role', ['roles' => $data]); // Sesuaikan dengan view yang Anda gunakan
    }

    // Simpan data dev role baru
    public function store(Request $request)
    {
        $request->validate([
            'roleName' => 'required|string|max:100|unique:devRoles,roleName'
        ], [
            'roleName.required' => 'Mohon isi nama role.',
            'roleName.max' => 'Nama role harus 100 huruf atau dibawah.',
            'roleName.unique' => 'Nama role sudah dipakai.'
        ]);

        DevRole::create([
            'roleName' => $request->roleName
        ]);

        return redirect()->back()->with('success', 'Dev Role berhasil ditambahkan!');
    }

    // Update data dev role
    public function update(Request $request, $id)
    {
        $request->validate([
            // ignore current row id when checking uniqueness
            'roleName' => 'required|string|max:100|unique:devRoles,roleName,' . $id . ',id'
        ], [
            'roleName.required' => 'Mohon isi nama role.',
            'roleName.max' => 'Nama role harus 100 huruf atau dibawah.',
            'roleName.unique' => 'Nama role sudah dipakai.'
        ]);

        $role = DevRole::findOrFail($id);
        $role->update([
            'roleName' => $request->roleName
        ]);

        return redirect()->back()->with('success', 'Dev Role berhasil diupdate!');
    }

    // Hapus data dev role
    public function destroy($id)
    {
        $role = DevRole::findOrFail(id: $id);
        $role->isDeleted = 1;
        $role->save();

        return redirect()->back()->with('success', 'Dev Role berhasil dihapus!');
    }

    public function validateStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $rules = [
            'roleName' => 'required|string|max:100|unique:devRoles,roleName'
        ];

        $messages = [
            'roleName.required' => 'Mohon isi nama role.',
            'roleName.max' => 'Nama role harus 100 huruf atau dibawah.',
            'roleName.unique' => 'Nama role sudah dipakai.'
        ];

        $validator = \Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function validateUpdate(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $rules = [
            'roleName' => 'required|string|max:100|unique:devRoles,roleName,' . $id . ',id'
        ];

        $messages = [
            'roleName.required' => 'Mohon isi nama role.',
            'roleName.max' => 'Nama role harus 100 huruf atau dibawah.',
            'roleName.unique' => 'Nama role sudah dipakai.'
        ];

        $validator = \Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
