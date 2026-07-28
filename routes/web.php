<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AkunAdminController;
use App\Http\Controllers\AkunSupportController;
use App\Http\Controllers\AkunSupervisorController;
use App\Http\Controllers\ClientTicketController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ChatDevController;
use App\Http\Controllers\ChatHistoryController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\SPVTicketController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SupportClientChatController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\DevNotesController;
use App\Http\Controllers\ManualPasswordResetController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        switch (auth()->user()->privLevel) {
            case 'admin':
                return redirect()->route('akunadmin');
            case 'spv':
                return redirect()->route('SPV.spv');
            case 'developer':
                return redirect()->route('Developer.developer');
            case 'support':
                return redirect()->route('Support.support');
            case 'super_admin':
                return redirect()->route('admin.admin');
            default:
                return redirect('/login')->withErrors(['Unauthorized role']);
        }
    }
    return redirect()->route('login');
});

//Dashboard admin : Akun Admin
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

    Route::delete('/dashboard/admin/akun/user/hapus-akun/{username}', [AkunAdminController::class, 'hapusData']);
    Route::get('/akun/edit/{username}', [AkunAdminController::class, 'edit'])->name('akun.edit');
    Route::post('/akun/update-data/{username}', [AkunAdminController::class, 'update'])->name('akun.update');

    Route::get('/akun/update-data/{username}', [AkunAdminController::class, 'edit']);
    Route::get('/dashboard/admin/akun/user/hapus-akun/{username}', [AkunAdminController::class, 'index']);

    Route::get('/admin/support/edit-data/{username}', [AkunSupportController::class, 'edit']);
    Route::post('/admin/support/update-data/{username}', [AkunSupportController::class, 'update']);
    Route::delete('/admin/support/user/hapus-akun/{username}', [AkunSupportController::class, 'hapusData']);

    Route::get('/admin/support/update-data/{username}', [AkunSupportController::class, 'edit']);
    Route::get('/admin/support/user/hapus-akun/{username}', [AkunSupportController::class, 'index']);

    Route::delete('/admin/supervisor/user/hapus-akun/{username}', [AkunSupervisorController::class, 'hapusData']);
    Route::post('/admin/supervisor/validate', [AkunSupervisorController::class, 'validateCreate'])->name('admin.supervisor.validate');
    Route::get('/admin/supervisor/edit-data/{username}', [AkunSupervisorController::class, 'edit']);
    Route::post('/admin/supervisor/update-data/{username}', [AkunSupervisorController::class, 'update']);

    Route::get('/admin/supervisor/validate', [AkunSupervisorController::class, 'index']);
    Route::get('/admin/supervisor/user/hapus-akun/{username}', [AkunSupervisorController::class, 'index']);
    Route::get('/admin/supervisor/update-data/{username}', [AkunSupervisorController::class, 'edit']);

    Route::get('/admin/ticketsupport', [SupportTicketController::class, 'index'])->name('supportticket');
    Route::delete('/admin/ticketsupport/hapus/{id}', [SupportTicketController::class, 'hapusData'])->name('supportticketHapus');

    Route::get('/admin/ticketsupport/hapus/{id}', [SupportTicketController::class, 'index']);

    Route::get('/admin/ticketclient', [ClientTicketController::class, 'index'])->name('clientticket');
    Route::delete('/admin/ticketclient/hapus/{id}', [ClientTicketController::class, 'hapusData'])->name('clientticketHapus');

    Route::get('/admin/ticketclient/hapus/{id}', [ClientTicketController::class, 'index']);

    // Role
    Route::get('/admin/role', [RoleController::class, 'index'])->name('role.role');
    Route::post('/admin/role/store', [RoleController::class, 'store'])->name('role.store');
    Route::post('/admin/role/update/{id}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('/admin/role/delete/{id}', [RoleController::class, 'destroy'])->name('role.delete');

    Route::get('/admin/role/store', [RoleController::class, 'index']);
    Route::get('/admin/role/update/{id}', [RoleController::class, 'index']);
    Route::get('/admin/role/delete/{id}', [RoleController::class, 'index']);
    Route::get('/admin/role/validate', [RoleController::class, 'index']);
    Route::get('/admin/role/update/{id}/validate', [RoleController::class, 'index']);

    Route::post('/admin/role/validate', [RoleController::class, 'validateStore'])->name('role.validate');
    Route::post('/admin/role/update/{id}/validate', [RoleController::class, 'validateUpdate'])->name('role.validateUpdate');

    Route::get('/admin/application', [ApplicationController::class, 'index'])->name('app.app');
    Route::post('/admin/application/store', [ApplicationController::class, 'store'])->name('app.store');
    Route::post('/admin/application/update/{id}', [ApplicationController::class, 'update'])->name('app.update');
    Route::delete('/admin/application/delete/{id}', [ApplicationController::class, 'destroy'])->name('app.delete');

    Route::get('/admin/application/store', [ApplicationController::class, 'index']);
    Route::get('/admin/application/update/{id}', [ApplicationController::class, 'index']);
    Route::get('/admin/application/delete/{id}', [ApplicationController::class, 'index']);

    Route::get('/admin/application/update/{id}/validate', [ApplicationController::class, 'index']);
    Route::get('/admin/application/validate', [ApplicationController::class, 'index']);

    Route::post('/admin/application/update/{id}/validate', [ApplicationController::class, 'validateUpdate'])->name('app.validateUpdate');
    Route::post('/admin/application/validate', [ApplicationController::class, 'validateStore'])->name('app.validate');

    Route::get('/admin/password', [ManualPasswordResetController::class, 'index']);
    Route::get('/admin/password/{id}', [ManualPasswordResetController::class, 'index']);
    Route::post('/admin/password/{id}', [ManualPasswordResetController::class, 'update'])->name('noemailPass.update');
    Route::get('/admin/password/{id}/validate', [ManualPasswordResetController::class, 'index']);
    Route::post('/admin/password/{id}/validate', [ManualPasswordResetController::class, 'validateUpdate']);
});

//Dashboard admin : Akun Developer
Route::middleware(['admin'])->group(function () {
    Route::get('/admin/akun', [AkunAdminController::class, 'index'])->name('akunadmin');
    Route::post('/admin/akun/tambah-data', [AkunAdminController::class, 'save']);
    Route::post('/admin/akun/validate', [AkunAdminController::class, 'validateCreate'])->name('admin.akun.validate');

    Route::get('/admin/akun/tambah-data', [AkunAdminController::class, 'index'])->name('akunadmin-tambah');
    Route::get('/admin/akun/validate', [AkunAdminController::class, 'index'])->name('akunadmin-val');

    //Dashboard admin : Akun Support
    Route::get('/admin/support', [AkunSupportController::class, 'index'])->name('akun.support');
    Route::post('/admin/support/tambah-data', [AkunSupportController::class, 'save']);
    Route::post('/admin/support/validate', [AkunSupportController::class, 'validateCreate'])->name('admin.support.validate');

    Route::get('/admin/support/tambah-data', [AkunSupportController::class, 'index']);
    Route::get('/admin/support/validate', [AkunSupportController::class, 'index']);

    //Dashboard admin : Ticket Support
    // Route::Post('/admin/akun/tambah-data', [SupportTicketController::class, 'save']);
    // Route::delete('/dashboard/admin/akun/user/hapus-akun/{username}', [SupportTicketController::class, 'hapusData']);
    // Route::get('/akun/edit/{username}', [SupportTicketController::class, 'edit'])->name('akun.edit');
    // Route::post('/akun/update-data/{username}', [SupportTicketController::class, 'update'])->name('akun.update');

    //Dashboard admin : Akun Supervisor
    Route::get('/admin/supervisor', [AkunSupervisorController::class, 'index'])->name('akun.supervisor');
    Route::post('/admin/supervisor/tambah-data', [AkunSupervisorController::class, 'save']);
    Route::post('/admin/supervisor/validate', [AkunSupervisorController::class, 'validateCreate'])->name('admin.supervisor.validate');

    Route::get('/admin/supervisor/tambah-data', [AkunSupervisorController::class, 'index']);
    Route::get('/admin/supervisor/validate', [AkunSupervisorController::class, 'index']);
});

//chat dev -- belum done (revisi terus)
Route::middleware(['dev'])->group(function () {
    Route::get('/dev/chatdev', [ChatDevController::class, 'index'])->name('Chatdev.chatdev');
    Route::post('/dev/chatdev/store', [ChatDevController::class, 'store'])->name('Chatdev.store');
    Route::post('/dev/chatdev/store-realtime', [ChatDevController::class, 'storeRealtime'])->name('Chatdev.storeRealtime');
    Route::get('/dev/taskticket', [DeveloperController::class, 'index'])->name('Developer.developer');
    Route::post('/dev/chatdev/resolve/{id}', [ChatDevController::class, 'resolveTicket'])->name('Chatdev.resolve');
    Route::post('/dev/chatdev/undo/{id}', [ChatDevController::class, 'undoTicket'])->name('Chatdev.undo');
    Route::post('/dev/chatdev/reopen/{id}', [ChatDevController::class, 'reopenTicket'])->name('Chatdev.reopen');
    Route::get('/developer/ticket/{id}', [DeveloperController::class, 'detail'])->name('developer.ticket.detail');
    Route::post('/developer/ticket/{id}/claim', [DeveloperController::class, 'claim'])->name('developer.ticket.claim');
    Route::post('/dev/chatdev/read/{t}', [ChatDevController::class, 'readIDinRealtime']);
    // Read-only chat history with note feature
    Route::get('/dev/chat-history', [ChatHistoryController::class, 'index'])->name('ChatHistory.index');
    Route::post('/dev/chat-history/note', [ChatHistoryController::class, 'storeNote'])->name('ChatHistory.note.store');
    Route::post('/dev/chat-history/note/delete', [ChatHistoryController::class, 'deleteNote'])->name('ChatHistory.note.delete');

    Route::get('/dev/chatdev/store', [ChatDevController::class, 'index']);
    Route::get('/dev/chatdev/store-realtime', [ChatDevController::class, 'index']);
    Route::get('/dev/chatdev/resolve/{id}', [ChatDevController::class, 'index']);
    Route::get('/dev/chatdev/read/{t}', [ChatDevController::class, 'index']);
    Route::get('/developer/ticket/{id}/claim', [DeveloperController::class, 'index']);
    Route::get('/dev/chat-history/note', [ChatHistoryController::class, 'index']);
    Route::get('/dev/chat-history/note/delete', [ChatHistoryController::class, 'index']);
});
//support chat
Route::middleware(['support'])->group(function () {
    Route::get('/Support/chat', [SupportChatController::class, 'index'])->name('Chatsup.chatsup');
    Route::post('/Support/chat/store', [SupportChatController::class, 'store'])->name('Chatsup.store');
    Route::post('/Support/chat/resolve/{id}', [SupportChatController::class, 'resolveTicket'])->name('Chatsup.resolve');
    Route::post('/Support/chat/reopen/{id}', [SupportChatController::class, 'reopenTicket'])->name('Chatsup.reopen');
    Route::post('/Support/chat/read/{t}', [SupportChatController::class, 'readIDinRealtime']);
    Route::post('/Support/chat/store-realtime', [SupportChatController::class, 'storeRealtime'])->name('Chatsup.storeRealtime');

    Route::get('/Support/chat/store', [SupportChatController::class, 'index'])->name('Chatsup.storeIndex');
    Route::get('/Support/chat/resolve/{id}', [SupportChatController::class, 'index'])->name('Chatsup.resolveIndex');
    Route::get('/Support/chat/read/{t}', [SupportChatController::class, 'index'])->name('Chatsup.readIndex');
    Route::get('/Support/chat/store-realtime', [SupportChatController::class, 'index'])->name('Chatsup.storeRealtimeIndex');

    Route::get('/support/ticket', [SupportController::class, 'support'])->name('Support.support');
    Route::post('/support/ticket/getTicket/{id}', [SupportController::class, 'takeTicket'])->name('Support.takeTicket');
    Route::get('/support/clientchat', [SupportClientChatController::class, 'index'])->name('SupportClientChat.supportclientchat');
    Route::post('/support/clientchat/store', [SupportClientChatController::class, 'store'])->name('SupportClientChat.store');
    Route::post('/Support/clientchat/resolve/{id}', [SupportClientChatController::class, 'resolveTicket'])->name('SupportClientChat.resolve');
    Route::post('/support/clientchat/read/{t}', [SupportClientChatController::class, 'readIDinRealtime']);
    Route::post('/support/clientchat/store-realtime', [SupportClientChatController::class, 'storeRealtime'])->name('SupportClientChat.storeRealtime');
    Route::post('/support/clientchat/dev-ticket', [SupportClientChatController::class, 'createDevTicket'])->name('SupportClientChat.createDevTicket');
    Route::post('/support/clientchat/copy-dev-ticket', [SupportClientChatController::class, 'copyDevTicket'])->name('SupportClientChat.copyDevTicket');
    Route::get('/support/devtickets/create', [SupportClientChatController::class, 'createDevTicketPage'])->name('SupportClientChat.createDevTicketPage');
    Route::post('/support/devtickets/create', [SupportClientChatController::class, 'validateDevTicket'])->name('SupportClientChat.validateDevTicket');

    Route::get('/support/clientchat/store', [SupportClientChatController::class, 'index'])->name('SupportClientChat.storeIndex');
    Route::get('/support/clientchat/resolve/{id}', [SupportClientChatController::class, 'index'])->name('SupportClientChat.resolveIndex');
    Route::get('/support/clientchat/read/{t}', [SupportClientChatController::class, 'index'])->name('SupportClientChat.readIndex');
    Route::get('/support/clientchat/store-realtime', [SupportClientChatController::class, 'index'])->name('SupportClientChat.storeRealtimeIndex');
    Route::get('/support/clientchat/dev-ticket', [SupportClientChatController::class, 'index'])->name('SupportClientChat.devTicketIndex');
});

Route::middleware(['images'])->group(function () {
    Route::get('/files/{filename}', [FileController::class, 'download'])->name('files.download');
    Route::get('/images/{filename}', [FileController::class, 'view'])->name('images.view');
    Route::get('/users/{filename}', [FileController::class, 'pfp'])->name('users');
    Route::get('/files/sup/{filename}', [FileController::class, 'downloadSupport'])->name('files.downloadSupport');
    Route::get('/images/sup/{filename}', [FileController::class, 'viewSupport'])->name('images.viewSupport');
});

Route::middleware(['support-images'])->group(function () {
    Route::get('/files/support/{filename}', [FileController::class, 'downloadSupportChat'])->name('files.downloadSupportChat');
    Route::get('/images/support/{filename}', [FileController::class, 'viewSupportChat'])->name('images.viewSupportChat');
    Route::get('/files/dev/{filename}', [FileController::class, 'downloadDev'])->name('files.downloadDev');
    Route::get('/images/dev/{filename}', [FileController::class, 'viewDev'])->name('images.viewDev');
});

// view only

Route::get('/spv-tickets', [SPVTicketController::class, 'index'])->middleware('spv')->name('SPV.spv');
Route::post('/spv-tickets/save', [SPVTicketController::class, 'saveData'])->middleware('spv')->name('SPV.save');
Route::get('/spv-tickets/save', [SPVTicketController::class, 'index'])->middleware('spv')->name('SPV.spvIdx');

Route::get('/logout', function () {
    if (auth()->check()) {
        switch (auth()->user()->privLevel) {
            case 'admin':
                return redirect()->route('akunadmin');
            case 'spv':
                return redirect()->route('SPV.spv');
            case 'developer':
                return redirect()->route('Developer.developer');
            case 'support':
                return redirect()->route('Support.support');
            case 'super_admin':
                return redirect()->route('admin.admin');
            default:
                return redirect('/login')->withErrors(['Unauthorized role']);
        }
    }
    return redirect()->route('login');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// auth -- view only
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('loginPost');
    Route::get('/forgot-password', [PasswordResetController::class, 'index'])->name('forgotPassword');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendEmail'])->name('sendEmail');
    Route::get('/password/reset/{token}', [NewPasswordController::class, 'index'])->name('newPassword');
    Route::post('/password/reset', [NewPasswordController::class, 'changePassword'])->name('updatePassword');
});

//Time Zone Setting
Route::post('/set-timezone', function (\Illuminate\Http\Request $request) {
    session(['user_timezone' => $request->timezone]);
    return response()->json(['status' => 'ok']);
});

Route::get('/set-timezone', function () {
    if (auth()->check()) {
        switch (auth()->user()->privLevel) {
            case 'admin':
                return redirect()->route('akunadmin');
            case 'spv':
                return redirect()->route('SPV.spv');
            case 'developer':
                return redirect()->route('Developer.developer');
            case 'support':
                return redirect()->route('Support.support');
            case 'super_admin':
                return redirect()->route('admin.admin');
            default:
                return redirect('/login')->withErrors(['Unauthorized role']);
        }
    }
    return redirect()->route('login');
});

Route::middleware(['dev'])->prefix('dev')->group(function () {
    Route::get('/notes', [DevNotesController::class, 'index'])->name('DevNotes.index');
    Route::post('/notes/create', [DevNotesController::class, 'create'])->name('DevNotes.create');
    Route::post('/notes/{note}/save', [DevNotesController::class, 'save'])->name('DevNotes.save');
    Route::post('/notes/{note}/rename', [DevNotesController::class, 'rename'])->name('DevNotes.rename');
    Route::delete('/notes/{note}', [DevNotesController::class, 'destroy'])->name('DevNotes.destroy');
    Route::get('/notes/{note}/download', [DevNotesController::class, 'download'])->name('DevNotes.download');

    Route::get('/notes/create', [DevNotesController::class, 'index']);
    Route::get('/notes/{note}/save', [DevNotesController::class, 'index']);
    Route::get('/notes/{note}/rename', [DevNotesController::class, 'index']);
    Route::get('/notes/{note}', [DevNotesController::class, 'index']);
});

Route::post('/online', [UserController::class, 'keepAlive'])->middleware('auth');

Route::get('/online', function () {
    if (auth()->check()) {
        switch (auth()->user()->privLevel) {
            case 'admin':
                return redirect()->route('akunadmin');
            case 'spv':
                return redirect()->route('SPV.spv');
            case 'developer':
                return redirect()->route('Developer.developer');
            case 'support':
                return redirect()->route('Support.support');
            case 'super_admin':
                return redirect()->route('admin.admin');
            default:
                return redirect('/login')->withErrors(['Unauthorized role']);
        }
    }
    return redirect()->route('login');
});

Route::prefix('support/clientchat')->name('SupportClientChat.')->middleware('support')->group(function () {
    Route::get('/', [SupportClientChatController::class, 'index'])->name('supportclientchat');
    // Route::post('/message', [SupportClientChatController::class, 'store'])->name('store');
    // Route::post('/{id}/resolve', [SupportClientChatController::class, 'resolve'])->name('resolve');
    Route::post('/dev-ticket', [SupportClientChatController::class, 'createDevTicket'])->name('createDevTicket');
    Route::get('/dev-ticket', [SupportClientChatController::class, 'index']);
});
