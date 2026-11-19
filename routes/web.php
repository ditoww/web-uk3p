<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\ManualPasswordResetController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes - UKKKP UNESA
|--------------------------------------------------------------------------
| Peran aktif: super_admin dan admin.
| Peran spv, developer, dan support sudah dicabut.
*/

// ===== Website publik =====
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/profil', [PublicController::class, 'profil'])->name('profil');
Route::get('/kegiatan', [PublicController::class, 'kegiatan'])->name('kegiatan');
Route::get('/kontak', [PublicController::class, 'kontak'])->name('kontak');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware('auth')->name('dashboard');

// ===== Super admin: akun admin dan reset password =====
Route::middleware(['superadmin'])->group(function () {
    Route::get('/admin/admin', [AdminController::class, 'index'])->name('admin.admin');
    Route::post('/admin/admin/validate', [AdminController::class, 'validateCreate'])->name('admin.admin.validate');
    Route::post('/admin/admin/tambah-data', [AdminController::class, 'save']);
    Route::get('/admin/admin/edit-data/{username}', [AdminController::class, 'edit']);
    Route::post('/admin/admin/update-data/{username}', [AdminController::class, 'update']);
    Route::delete('/admin/admin/user/hapus-akun/{username}', [AdminController::class, 'hapusData']);

    Route::get('/admin/admin/update-data/{username}', [AdminController::class, 'edit']);
    Route::get('/admin/admin/user/hapus-akun/{username}', [AdminController::class, 'index']);
    Route::get('/admin/admin/tambah-data', [AdminController::class, 'index']);
    Route::get('/admin/admin/validate', [AdminController::class, 'index']);

    Route::get('/admin/password', [ManualPasswordResetController::class, 'index']);
    Route::get('/admin/password/{id}', [ManualPasswordResetController::class, 'index']);
    Route::post('/admin/password/{id}', [ManualPasswordResetController::class, 'update'])->name('noemailPass.update');
    Route::get('/admin/password/{id}/validate', [ManualPasswordResetController::class, 'index']);
    Route::post('/admin/password/{id}/validate', [ManualPasswordResetController::class, 'validateUpdate']);
});

// ===== Kelola konten website (admin dan super admin) =====
Route::middleware(['auth'])->prefix('kelola')->name('kelola.konten.')->group(function () {
    $c = \App\Http\Controllers\KelolaKontenController::class;
    Route::get('/{page}', [$c, 'halaman'])->name('halaman')->where('page', 'beranda|profil|kegiatan|kontak');
    Route::post('/{page}/teks', [$c, 'simpanTeks'])->name('teks')->where('page', 'beranda|profil|kegiatan|kontak');
    Route::get('/item/{section}/tambah', [$c, 'tambah'])->name('tambah')->where('section', '[a-z_]+');
    Route::post('/item/{section}', [$c, 'simpan'])->name('simpan')->where('section', '[a-z_]+');
    Route::get('/item/{content}/ubah', [$c, 'ubah'])->name('ubah')->whereNumber('content');
    Route::put('/item/{content}', [$c, 'perbarui'])->name('perbarui')->whereNumber('content');
    Route::delete('/item/{content}', [$c, 'hapus'])->name('hapus')->whereNumber('content');
});
// ===== File dan foto profil =====
Route::middleware(['images'])->group(function () {
    Route::get('/files/{filename}', [FileController::class, 'download'])->name('files.download');
    Route::get('/images/{filename}', [FileController::class, 'view'])->name('images.view');
    Route::get('/users/{filename}', [FileController::class, 'pfp'])->name('users');
});

// ===== Login, logout, lupa password =====
Route::get('/logout', function () {
    return redirect()->route('home');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('loginPost');
    Route::get('/forgot-password', [PasswordResetController::class, 'index'])->name('forgotPassword');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendEmail'])->name('sendEmail');
    Route::get('/password/reset/{token}', [NewPasswordController::class, 'index'])->name('newPassword');
    Route::post('/password/reset', [NewPasswordController::class, 'changePassword'])->name('updatePassword');
});

// ===== Zona waktu dan status online =====
Route::post('/set-timezone', function (\Illuminate\Http\Request $request) {
    session(['user_timezone' => $request->timezone]);
    return response()->json(['status' => 'ok']);
});
Route::get('/set-timezone', function () {
    return redirect()->route('home');
});

Route::post('/online', [UserController::class, 'keepAlive'])->middleware('auth');
Route::get('/online', function () {
    return redirect()->route('home');
});