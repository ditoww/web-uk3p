<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\DevTicket;
use App\Models\ClientTicket;

use Imagick;

class AkunSupportController extends Controller
{
    public function index()
    {
        $admins = User::where('privLevel', 'support')->where('isDeleted', 0)->get();

        return view('Admin.support', [
            'alldata' => $admins
        ]);
    }

    // AJAX: validasi regex per-field (inline, tanpa popup)
    public function validateRegex(Request $request): JsonResponse
    {
        $field = $request->get('field');
        $value = $request->get('value');

        $rulesMap = [
            'username' => 'nullable|max:31|regex:/^[A-Za-z0-9]+$/',
            'nama'     => 'nullable|max:150|regex:/^[A-Za-z ]+$/',
            'email'    => 'nullable|email|max:191',
            'nohp'     => 'nullable|regex:/^[0-9]{10,13}$/',
        ];

        $messages = [
            'username.max'  => 'Panjang username harus 31 huruf atau dibawahnya.',
            'username.regex' => 'Username tersebut hanya menggunakan huruf besar, huruf kecil, dan angka.',
            'nama.max'      => 'Nama lengkap harus 150 huruf atau dibawah',
            'nama.regex'    => 'Nama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'email.email'   => 'Email tersebut tidak valid.',
            'email.max'     => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex'    => 'Nomor HP tersebut tidak valid.',
        ];

        if (!isset($rulesMap[$field])) {
            return response()->json([
                'ok' => false,
                'errors' => [$field => ['Field tidak dikenal.']]
            ], 422);
        }

        $validator = Validator::make(
            [$field => $value],
            [$field => $rulesMap[$field]],
            $messages
        );

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function validateCreate(Request $request): \Illuminate\Http\JsonResponse
    {
        $rules = [
            'username' => 'required|unique:users|max:31|regex:/^[A-Za-z0-9]+$/',
            'nama' => 'required|max:150|regex:/^[A-Za-z ]+$/',
            'email' => 'nullable|email|max:191',
            'nohp' => 'nullable|regex:/^[0-9]{10,13}$/',
            'pass' => 'required|max:191',
            'tgl' => 'nullable|date',
            'status' => 'nullable|in:active,Away',
            'upload' => 'nullable|mimes:jpg,png,jpeg|max:5120',
        ];

        $messages = [
            'username.required' => 'Mohon isi username untuk Support.',
            'username.unique' => 'Username tersebut sudah dipakai.',
            'username.max' => 'Panjang username harus 31 huruf atau dibawahnya.',
            'username.regex' => 'Username tersebut hanya menggunakan huruf besar, huruf kecil, dan angka.',
            'nama.required' => 'Mohon isi nama lengkap untuk Support.',
            'nama.max' => 'Nama lengkap harus 150 huruf atau dibawah',
            'nama.regex' => 'Nama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'email.email' => 'Email tersebut tidak valid.',
            'email.max' => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex' => 'Nomor HP tersebut tidak valid.',
            'pass.required' => 'Mohon isi password untuk Support.',
            'pass.max' => 'Password harus dibawah 192 huruf.',
            'upload.max' => 'Gambar Profile Picture tersebut harus dibawah 5MB.',
            'upload.mimes' => 'Gambar Profile Picture harus jpg, png, atau jpeg.'
        ];

        $validator = \Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function save(Request $request)
    {
        $rules = [
            'username' => 'required|unique:users|max:31|regex:/^[A-Za-z0-9]+$/',
            'nama' => 'required|max:150|regex:/^[A-Za-z ]+$/',
            'email' => 'nullable|email|max:191',
            'nohp' => 'nullable|regex:/^[0-9]{10,13}$/',
            'pass' => 'required|max:191',
            'tgl' => 'nullable|date',
            'status' => 'nullable|in:active,Away',
            'upload' => 'nullable|mimes:jpg,png,jpeg|max:5120',
        ];

        $messages = [
            'username.required' => 'Mohon isi username untuk Support.',
            'username.unique' => 'Username tersebut sudah dipakai.',
            'username.max' => 'Panjang username harus 31 huruf atau dibawahnya.',
            'username.regex' => 'Username tersebut hanya menggunakan huruf besar, huruf kecil, dan angka.',
            'nama.required' => 'Mohon isi nama lengkap untuk Support.',
            'nama.max' => 'Nama lengkap harus 150 huruf atau dibawah',
            'nama.regex' => 'Mama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'email.email' => 'Email tersebut tidak valid.',
            'email.max' => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex' => 'Nomor HP tersebut tidak valid.',
            'pass.required' => 'Mohon isi password untuk Support.',
            'pass.max' => 'Password harus dibawah 192 huruf.',
            'upload.max' => 'Gambar Profile Picture tersebut harus dibawah 5MB.',
            'upload.mimes' => 'Gambar Profile Picture harus jpg, png, atau jpeg.'
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->route('akun.support')->withErrors($validator)->withInput()->with('error', "Terdapat sebuah masalah dalam penambahan akun. Klik tombol Tambah Data untuk mengetahui masalahnya.");
        }

        $gambarUrl = null;
        if ($request->hasFile('upload')) {
            // Ensure destination directory exists
            $destDir = public_path('GambarProfileAdmin/');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            $gambar = $request->file('upload');
            $namaGambar = date('Ymd') . '_' . uniqid() . '.' . $gambar->getClientOriginalExtension();
            $ext = strtolower($gambar->getClientOriginalExtension());
            $imagick = new Imagick();
            $imagick->readImage($gambar->getRealPath());
            if (!empty($imagick->getImageProperties('exif:*'))) $imagick->stripImage();
            $w = $imagick->getImageWidth();
            $h = $imagick->getImageHeight();
            $maxDim = 1920;

            $savePath = $destDir . $namaGambar;

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
                throw new \RuntimeException('Imagick failed to write image.');
            }

            $imagick->clear();
            $imagick->destroy();

            $gambarUrl = asset('GambarProfileAdmin/' . $namaGambar);
        }

        $user = new User();
        $user->username = $request->username;
        $user->password = Hash::make($request->pass);
        $user->name = $request->nama;
        $user->email = $request->filled('email') ? $request->email : null;
        $user->phone_number = $request->filled('nohp') ? $request->nohp : null;
        $user->privLevel = 'support';
        $user->status = $request->status ?? 'Away';
        $user->created_at = now();
        $user->updated_at = now();
        $user->profile_picture = $gambarUrl;
        $user->save();

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Data Akun Telah Ditambahkan!']);
        }

        return redirect()->route('akun.support')->with('success', 'Data Akun Telah Ditambahkan!');
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
                if (DevTicket::where('supportID', $user->id)->exists() || ClientTicket::where('supportID', $user->id)->exists()) {
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

        if (!$akun) {
            return redirect()->route('akun.support')->with('error', 'Akun tidak ditemukan.');
        }

        return view('Support.edit', [
            'akun' => $akun,
        ]);
    }

    public function update(Request $request, $username)
    {
        $akun = User::where('username', $username)->first();

        if (!$akun) {
            return redirect()->route('akun.support')->with('error', 'Akun tidak ditemukan.');
        }

        $request->validate([
            'nama' => 'required|max:150|regex:/^[A-Za-z ]+$/',
            'email' => 'nullable|email|max:191',
            'nohp' => 'nullable|regex:/^[0-9]{10,13}$/',
            'upload' => 'nullable|mimes:jpg,jpeg,png|max:5120',
            'password' => 'required'
        ], [
            'nama.required' => 'Mohon isi nama lengkap untuk Support.',
            'nama.regex' => 'Mama lengkap hanya menggunakan huruf besar, huruf kecil, dan spasi.',
            'email.email' => 'Email tersebut tidak valid.',
            'password.required' => 'Mohon isi password untuk Supervisor.',
            'email.max' => 'Email perlu 191 huruf atau kebawah.',
            'nohp.regex' => 'Nomor HP tersebut tidak valid.',
            'upload.max' => 'Gambar Profile Picture tersebut harus dibawah 5MB.',
            'upload.mimes' => 'Gambar Profile Picture harus jpg, png, atau jpeg.'
        ]);

        if ($request->hasFile('upload')) {
            // Ensure destination directory exists
            $destDir = public_path('GambarProfileAdmin/');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            if ($akun->profile_picture) {
                $oldImage = public_path('GambarProfileAdmin/' . basename($akun->profile_picture));
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
            }

            $file = $request->file('upload');
            $filename = date('Ymd') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $ext = strtolower($file->getClientOriginalExtension());
            $imagick = new Imagick();
            $imagick->readImage($file->getRealPath());
            if (!empty($imagick->getImageProperties('exif:*'))) $imagick->stripImage();

            $w = $imagick->getImageWidth();
            $h = $imagick->getImageHeight();
            $maxDim = 1920;

            $savePath = $destDir . $filename;
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
        $akun->email = $request->filled('email') ? $request->email : null;
        $akun->phone_number = $request->filled('nohp') ? $request->nohp : null;
        $akun->updated_at = now();
        $akun->save();

        return redirect()->route('akun.support')->with('success', 'Data berhasil diperbarui.');
    }
}
