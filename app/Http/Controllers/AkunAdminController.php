<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\DevRole;
use App\Models\DevTicket;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Imagick;

class AkunAdminController extends Controller
{
    public function index()
    {
        $admins = \DB::table('users')
            ->leftJoin('devRoles', 'users.devRoleID', '=', 'devRoles.id')
            ->where('users.privLevel', 'developer')
            ->where('users.isDeleted', 0)
            ->select('users.*', 'devRoles.roleName')
            ->get();

        return view('Admin.akun', [
            'alldata' => $admins,
            'roles' => DevRole::where('isDeleted', 0)->get(),
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'username' => 'required|unique:users|max:31|regex:/^[A-Za-z0-9]+$/',
            'nama' => 'required|max:150||regex:/^[A-Za-z ]+$/',
            'email' => 'nullable|email|max:191',
            'nohp' => 'nullable|regex:/^[0-9]{10,13}$/',
            'role' => 'required|exists:devRoles,id',
            'pass' => 'required',
            'tgl' => 'nullable|date',
            'status' => 'nullable|in:active,Away',
            'upload' => 'nullable|mimes:jpg,png,jpeg|max:5120',
        ];

        $messages = [
            'username.required' => 'Mohon isi username untuk Developer.',
            'username.unique' => 'Username tersebut sudah dipakai.',
            'username.max' => 'Panjang username harus 255 huruf atau dibawahnya.',
            'username.regex' => 'Username tersebut hanya menggunakan huruf besar, huruf kecil, dan angka.',
            'nama.required' => 'Mohon isi nama lengkap untuk Developer.',
            'nama.regex' => 'Mama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'nama.max' => 'Nama lengkap harus 150 huruf atau dibawah.',
            'email.email' => 'Email tersebut tidak valid.',
            'email.max' => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex' => 'Nomor HP tidak valid.',
            'role.required' => 'Mohon pilih sebuah role untuk Developer.',
            'role.exists' => 'Role tersebut tidak tersimpan di database.',
            'pass.required' => 'Mohon isi password untuk Developer.',
            'tgl.date' => 'Tanggal tersebut bukan tanggal yang valid.',
            'upload.max' => 'Gambar Profile Picture tersebut harus dibawah 5MB.',
            'upload.mimes' => 'Gambar Profile Picture harus jpg, png, atau jpeg.'
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) return redirect()->route('akunadmin')->withErrors($validator)->withInput()->with('error', "Terdapat sebuah masalah dalam penambahan akun. Klik tombol Tambah Data untuk mengetahui masalahnya.");

        $gambarUrl = null;
        if ($request->hasFile('upload')) {
            $gambar = $request->file('upload');
            $ext = strtolower($gambar->getClientOriginalExtension());
            $namaGambar = date('Ymd') . '_' . uniqid() . '.' . $gambar->getClientOriginalExtension();
            $imagick = new Imagick();
            $imagick->readImage($gambar->getRealPath());
            if (!empty($imagick->getImageProperties('exif:*'))) $imagick->stripImage();

            $folderPath = public_path('GambarProfileAdmin');
            if (!is_dir($folderPath)) mkdir($folderPath, 0755, true);
            $savePath = $folderPath . '/' . $namaGambar;

            $w = $imagick->getImageWidth();
            $h = $imagick->getImageHeight();
            $maxDim = 1920;

            if ($w > $maxDim || $h > $maxDim) {
                $ratio = min($maxDim / $w, $maxDim / $h);
                $imagick->resizeImage((int)($w * $ratio), (int)($h * $ratio), \Imagick::FILTER_LANCZOS, 1);
            }

            if ($ext === 'jpg' || $ext === 'jpeg') {
                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(82);
            } elseif ($ext === 'png') {
                $imagick->setOption('png:compression-level', '9');
                $imagick->setOption('png:compression-filter', '5');
                if (($w * $h) > (1280 * 1280)) {
                    $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                }
            }

            if (!$imagick->writeImage($savePath)) {
                throw new Exception('Imagick failed to write image.');
            }

            $imagick->clear();
            $imagick->destroy();

            $gambarUrl = asset('GambarProfileAdmin/' . $namaGambar);
        }

        $user = new User();
        $user->username = $request->username;
        $user->password = Hash::make($request->pass);
        $user->name = $request->nama;
        $user->email = $request->email;
        $user->phone_number = $request->nohp;
        $user->privLevel = 'developer';
        $user->devRoleID = $request->role; // pastikan ini ID dari devRoles
        $user->status = $request->status ?? 'Away';
        $user->created_at = now();
        $user->updated_at = now();
        $user->profile_picture = $gambarUrl;
        $user->save();

        return redirect()->route('akunadmin')->with('success', 'Data Akun Telah Ditambahkan!');
    }

    public function hapusData($username)
    {
        $user = User::where('username', $username)->first();

        if ($user) {
            try {
                /*if ($user->profile_picture) {
                    $namaGambar = basename($user->profile_picture);
                    $gambarPath = public_path('GambarProfileAdmin/' . $namaGambar);
                    if (file_exists($gambarPath)) {
                        unlink($gambarPath);
                    }
                }*/

                if (DevTicket::where('devID', $user->id)->exists()) {
                    $user->isDeleted = 1;
                    $user->save();
                } else {
                    $user->delete();
                }

                return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => 'Data tidak dapat dihapus karena berelasi dengan data lainnya!'
                ]);
            }
        }

        return response()->json(['error' => true, 'message' => 'Data tidak ditemukan!']);
    }

    public function edit($username)
    {
        $akun = User::where('username', $username)->first();
        $roles = DevRole::where('isDeleted', 0)->get();

        if (!$akun) {
            return redirect()->route('akunadmin')->with('error', 'Akun tidak ditemukan.');
        }

        return view('Admin.akun_edit', [
            'akun' => $akun,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, $username)
    {
        $akun = User::where('username', $username)->first();

        if (!$akun) {
            return redirect()->route('akunadmin')->with('error', 'Akun tidak ditemukan.');
        }

        $request->validate([
            'nama' => 'required|max:150|regex:/^[A-Za-z ]+$/',
            'email' => 'nullable|email|max:191',
            'nohp' => 'nullable|regex:/^[0-9]{10,13}$/',
            'role' => 'required|exists:devRoles,id',
            'password' => 'required',
            'upload' => 'nullable|mimes:jpg,jpeg,png|max:5120'
        ], [
            'nama.required' => 'Mohon isi nama lengkap untuk Developer.',
            'nama.regex' => 'Mama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'email.email' => 'Email tersebut tidak valid.',
            'email.max' => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex' => 'Nomor HP tersebut tidak valid.',
            'role.required' => 'Mohon pilih sebuah role untuk Developer.',
            'password.required' => 'Mohon isi password untuk Developer.',
            'role.exists' => 'Role tersebut tidak tersimpan di database.',
            'upload.max' => 'Gambar Profile Picture tersebut harus dibawah 5MB.',
            'upload.mimes' => 'Gambar Profile Picture harus jpg, png, atau jpeg.'
        ]);

        if ($request->hasFile('upload')) {
            if ($akun->profile_picture) {
                $oldImage = public_path('GambarProfileAdmin/' . basename($akun->profile_picture));
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
            }

            $file = $request->file('upload');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = date('Ymd') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagick = new Imagick();
            $imagick->readImage($file->getRealPath());
            if (!empty($imagick->getImageProperties('exif:*'))) $imagick->stripImage();

            $folderPath = public_path('GambarProfileAdmin');
            if (!is_dir($folderPath)) mkdir($folderPath, 0755, true);
            $savePath = $folderPath . '/' . $filename;

            $w = $imagick->getImageWidth();
            $h = $imagick->getImageHeight();
            $maxDim = 1920;

            if ($w > $maxDim || $h > $maxDim) {
                $ratio = min($maxDim / $w, $maxDim / $h);
                $imagick->resizeImage((int)($w * $ratio), (int)($h * $ratio), \Imagick::FILTER_LANCZOS, 1);
            }

            if ($ext === 'jpg' || $ext === 'jpeg') {
                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(82);
            } elseif ($ext === 'png') {
                $imagick->setOption('png:compression-level', '9');
                $imagick->setOption('png:compression-filter', '5');
                if (($w * $h) > (1280 * 1280)) {
                    $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                }
            }

            if (!$imagick->writeImage($savePath)) {
                throw new Exception('Imagick failed to write image.');
            }

            $imagick->clear();
            $imagick->destroy();

            $akun->profile_picture = asset('GambarProfileAdmin/' . $filename);
        }

        $akun->password = Hash::make($request->password);
        $akun->name = $request->nama;
        $akun->email = $request->email;
        $akun->phone_number = $request->nohp;
        $akun->devRoleID = $request->role;
        $akun->updated_at = now();
        $akun->save();

        return redirect()->route('akunadmin')->with('success', 'Data berhasil diperbarui.');
    }

    public function validateCreate(Request $request)
    {
        $rules = [
            'username' => 'required|unique:users|max:31|regex:/^[A-Za-z0-9]+$/',
            'nama' => 'required|max:150|regex:/^[A-Za-z ]+$/',
            'email' => 'nullable|email|max:191',
            'nohp' => 'nullable|regex:/^[0-9]{10,13}$/',
            'role' => 'required|exists:devRoles,id',
            'pass' => 'required|max:191',
            'tgl' => 'nullable|date',
            'status' => 'nullable|in:active,Away',
            'upload' => 'nullable|mimes:jpg,png,jpeg|max:5120',
        ];

        $messages = [
            'username.required' => 'Mohon isi username untuk Developer.',
            'username.unique' => 'Username tersebut sudah dipakai.',
            'username.max' => 'Panjang username harus 255 huruf atau dibawahnya.',
            'username.regex' => 'Username tersebut hanya menggunakan huruf besar, huruf kecil, dan angka.',
            'nama.required' => 'Mohon isi nama lengkap untuk Developer.',
            'nama.regex' => 'Mama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'nama.max' => 'Nama lengkap harus 150 huruf atau dibawah.',
            'email.email' => 'Email tersebut tidak valid.',
            'email.max' => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex' => 'Nomor HP tidak valid.',
            'role.required' => 'Mohon pilih sebuah role untuk Developer.',
            'role.exists' => 'Role tersebut tidak tersimpan di database.',
            'pass.required' => 'Mohon isi password untuk Developer.',
            'pass.max' => 'Password harus dibawah 192 huruf.',
            'tgl.date' => 'Tanggal tersebut bukan tanggal yang valid.',
            'upload.max' => 'Gambar Profile Picture tersebut harus dibawah 5MB.',
            'upload.mimes' => 'Gambar Profile Picture harus jpg, png, atau jpeg.'
        ];

        $validator = \Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
