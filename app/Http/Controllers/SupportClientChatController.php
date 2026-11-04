<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Models\ClientTicket;
use App\Models\ClientChat;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;
use App\Mail\CompleteMail;
use Carbon\Carbon;
use App\Rules\StrictDocx;
use App\Rules\StrictXlsx;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Imagick;
//use App\Services\WhatsAppNotificationService;
use App\Models\DevRole;


class SupportClientChatController extends Controller
{
    /**
     * Create a developer ticket derived from a client support ticket conversation
     * or from an external complaint (call, etc.) by picking a role.
     */
    public function createDevTicket(Request $request)
    {
        $rules = [
            'case_type' => 'required|in:ticket,external',
            'client_ticket_id' => 'required_if:case_type,ticket|nullable|integer|exists:clientTickets,id',
            'role_id' => 'required_if:case_type,external|nullable|integer|exists:devRoles,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            // Naikkan limit ke 5MB
            'image' => 'nullable|mimes:jpg,jpeg,png,webp,xlsx,docx,pdf|max:5120',
        ];

        $messages = [
            'case_type.required' => 'Mohon isi jenis kasus yang Anda mau lapor.',
            'client_ticket_id.required_if' => 'Mohon isikan nomor tiket yang dibuat oleh Client.',
            'client_ticket_id.exists' => 'Tiket dipilih tidak diketemui di dalam database atau belum dipilih.',
            'role_id.required_if' => 'Mohon isikan role developer yang akan menangani tiket Anda.',
            'role_id.exists' => 'Mohon isikan role developer yang akan menangani tiket Anda.',
            'title.required' => 'Mohon isi judul tiket tersebut.',
            'title.string' => 'Mohon isi judul yang valid.',
            'title.max' => 'Judul terlalu panjang. Mohon memendekkan judul tersebut.',
            'description.required' => 'Mohon isi deskripsi permasalahan pada tiket tersebut.',
            'description.max' => 'Deskripsi tersebut melewati batasan huruf 10000 karakter.',
            'image.mimes' => 'File tersebut tidak menggunakan format yang dapat di-support.',
            'image.max' => 'Ukuran maksimal file harus dibawah 5MB.'
        ];

        $validated = $request->validate($rules, $messages);

        $supportId = Auth::id();
        $title = $validated['title'];
        $desc = $validated['description'];

        if ($validated['case_type'] === 'ticket') {
            $clientTicketId = (int) $validated['client_ticket_id'];
            $clientTicket = DB::table('clientTickets')->where('id', $clientTicketId)->where('supportID', Auth::id())->first();
            if (!$clientTicket) {
                return back()->withErrors(['client_ticket_id' => 'Client ticket not found'])->withInput();
            }
            $roleId = DB::table('applications')->where('id', $clientTicket->appID)->value('roleID');
        } else {
            $clientTicketId = null;
            $roleId = (int) $validated['role_id']; // sudah divalidasi exists:devRoles,id
        }

        $ticketId = DB::table('devTickets')->insertGetId([
            'supportID' => $supportId,
            'devID' => null,
            'roleID' => $roleId,
            'title' => $title,
            'status' => 'Open',
            'link' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $chatId = DB::table('devChats')->insertGetId([
            'ticket_id' => $ticketId,
            'sender' => 'support',
            'response' => $desc,
            'check' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            // Safety hard limit (jika php.ini lebih besar)
            if ($img->getSize() > 5 * 1024 * 1024) {
                return back()->withErrors(['image' => 'File melebihi 5MB'])->withInput();
            }
            $size = $img->getSize();
            $ext = strtolower($img->getClientOriginalExtension());
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
            if ($ext === 'docx') {
                $rule = new StrictDocx();
                if (!$rule->passes('image.*', $img)) {
                    return back()->withErrors(['image' => 'File tidak valid'])->withInput();
                }
            }
            if ($ext === 'xlsx') {
                $rule = new StrictXlsx();
                if (!$rule->passes('image.*', $img)) {
                    return back()->withErrors(['image' => 'File tidak valid'])->withInput();
                }
            }
            $count = $isImage ? DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['jpg', 'jpeg', 'png', 'webp'])
                ->count()
                :   DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['docx', 'pdf', 'xlsx'])
                ->count();

            $now = now();
            $chatCreated = $now->clone();
            $datePrefix = 'TK-' . \Carbon\Carbon::parse($chatCreated)->format('Ymd');

            $fileName = $datePrefix . '-' . $chatId . '_' . ($count + 1) . '_' . $img->getClientOriginalName();
            $targetDir = $isImage ? public_path('chat_images') : public_path('chat_attachments');
            $targetPath = $targetDir . '/' . $fileName;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }
            if ($isImage) {
                $imagick = new \Imagick();
                $imagick->readImage($img->getRealPath());
                $imagick->stripImage();

                $w = $imagick->getImageWidth();
                $h = $imagick->getImageHeight();
                $maxDim = 1920;

                if ($w > $maxDim || $h > $maxDim) {
                    $ratio = min($maxDim / $w, $maxDim / $h);
                    $imagick->resizeImage((int)($w * $ratio), (int)($h * $ratio), \Imagick::FILTER_LANCZOS, 1);
                }
                // Extra downscale kualitas jika masih besar >1.5MB
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageCompressionQuality(82);
                    if ($size > 1500 * 1024) {
                        $imagick->setImageCompressionQuality(75);
                    }
                } elseif ($ext === 'webp') {
                    $imagick->setImageCompressionQuality($size > 1500 * 1024 ? 72 : 82);
                } elseif ($ext === 'png') {
                    $imagick->setOption('png:compression-level', '9');
                    $imagick->setOption('png:compression-filter', '5');
                    if (($w * $h) > (1280 * 1280)) {
                        $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                    }
                }

                if (!$imagick->writeImage($targetPath)) {
                    throw new \Exception('Imagick failed to write image.');
                }

                $imagick->clear();
                $imagick->destroy();

                $filePath = asset('chat_images/' . $fileName);
                $finalSize = @filesize($targetPath) ?: $size;
                DB::table('devAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $fileName,
                    'fileExtension' => $img->getClientOriginalExtension(),
                    'filePath' => $filePath,
                    'fileSize' => $finalSize,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $img->move(public_path('chat_attachments/'), $fileName);
                $filePath = asset('chat_attachments/' . $fileName);
                DB::table('devAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $fileName,
                    'fileExtension' => $img->getClientOriginalExtension(),
                    'filePath' => $filePath,
                    'fileSize' => $size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('Chatsup.chatsup', ["ticket_dev" => $ticketId])
            ->with('success', 'Dev ticket berhasil dibuat.');
    }

    public function copyDevTicket(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            // Naikkan limit ke 5MB
            'image' => 'nullable|mimes:jpg,jpeg,png,webp,xlsx,docx,pdf|max:5120',
            'ticket_id' => 'required'
        ];

        $messages = [
            'title.required' => 'Mohon isi judul tiket tersebut.',
            'title.string' => 'Mohon isi judul yang valid.',
            'title.max' => 'Judul terlalu panjang. Mohon memendekkan judul tersebut.',
            'description.required' => 'Mohon isi deskripsi permasalahan pada tiket tersebut.',
            'description.max' => 'Deskripsi tersebut melewati batasan huruf 10000 karakter.',
            'image.mimes' => 'File tersebut tidak menggunakan format yang dapat di-support.',
            'image.max' => 'Ukuran maksimal file harus dibawah 5MB.'
        ];

        $validated = $request->validate($rules, $messages);

        $supportId = Auth::id();
        $title = $validated['title'];
        $desc = $validated['description'];
        $devTicket = DB::table('devTickets')->where('id', $validated['ticket_id'])->first();
        $parentId = $devTicket->copyId;
        $copyKe = DB::table('devTickets')->where('id', $devTicket->id)->orderBy('copyCount', 'desc')->first();
        $copyCount = 0;
        if ($parentId == 0) {
            $cariData = DB::table('devTickets')->where('copyId', $devTicket->id)->orderBy('copyCount', 'desc')->first();
            if ($cariData) {
                $copyCount = $cariData->copyCount + 1;
            } else {
                $copyCount = 1;
            }
        } else {
            $copyCount = $copyKe->copyCount + 1;
        }

        $roleId = $devTicket->roleID; // sudah divalidasi exists:devRoles,id

        $ticketId = DB::table('devTickets')->insertGetId([
            'supportID' => $supportId,
            'devID' => null,
            'roleID' => $roleId,
            'title' => $title,
            'status' => 'Open',
            'link' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'copyId' => $devTicket->copyId == 0 ? $devTicket->id : $devTicket->copyId,
            'copyCount' => $copyCount,
        ]);

        $chatId = DB::table('devChats')->insertGetId([
            'ticket_id' => $ticketId,
            'sender' => 'support',
            'response' => $desc,
            'check' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            // Safety hard limit (jika php.ini lebih besar)
            if ($img->getSize() > 5 * 1024 * 1024) {
                return back()->withErrors(['image' => 'File melebihi 5MB'])->withInput();
            }
            $size = $img->getSize();
            $ext = strtolower($img->getClientOriginalExtension());
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
            if ($ext === 'docx') {
                $rule = new StrictDocx();
                if (!$rule->passes('image.*', $img)) {
                    return back()->withErrors(['image' => 'File tidak valid'])->withInput();
                }
            }
            if ($ext === 'xlsx') {
                $rule = new StrictXlsx();
                if (!$rule->passes('image.*', $img)) {
                    return back()->withErrors(['image' => 'File tidak valid'])->withInput();
                }
            }
            $count = $isImage ? DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['jpg', 'jpeg', 'png', 'webp'])
                ->count()
                :   DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['docx', 'pdf', 'xlsx'])
                ->count();

            $now = now();
            $chatCreated = $now->clone();
            $datePrefix = 'TK-' . \Carbon\Carbon::parse($chatCreated)->format('Ymd');

            $fileName = $datePrefix . '-' . $chatId . '_' . ($count + 1) . '_' . $img->getClientOriginalName();
            $targetDir = $isImage ? public_path('chat_images') : public_path('chat_attachments');
            $targetPath = $targetDir . '/' . $fileName;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }
            if ($isImage) {
                $imagick = new \Imagick();
                $imagick->readImage($img->getRealPath());
                $imagick->stripImage();

                $w = $imagick->getImageWidth();
                $h = $imagick->getImageHeight();
                $maxDim = 1920;

                if ($w > $maxDim || $h > $maxDim) {
                    $ratio = min($maxDim / $w, $maxDim / $h);
                    $imagick->resizeImage((int)($w * $ratio), (int)($h * $ratio), \Imagick::FILTER_LANCZOS, 1);
                }
                // Extra downscale kualitas jika masih besar >1.5MB
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageCompressionQuality(82);
                    if ($size > 1500 * 1024) {
                        $imagick->setImageCompressionQuality(75);
                    }
                } elseif ($ext === 'webp') {
                    $imagick->setImageCompressionQuality($size > 1500 * 1024 ? 72 : 82);
                } elseif ($ext === 'png') {
                    $imagick->setOption('png:compression-level', '9');
                    $imagick->setOption('png:compression-filter', '5');
                    if (($w * $h) > (1280 * 1280)) {
                        $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                    }
                }

                if (!$imagick->writeImage($targetPath)) {
                    throw new \Exception('Imagick failed to write image.');
                }

                $imagick->clear();
                $imagick->destroy();

                $filePath = asset('chat_images/' . $fileName);
                $finalSize = @filesize($targetPath) ?: $size;
                DB::table('devAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $fileName,
                    'fileExtension' => $img->getClientOriginalExtension(),
                    'filePath' => $filePath,
                    'fileSize' => $finalSize,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $img->move(public_path('chat_attachments/'), $fileName);
                $filePath = asset('chat_attachments/' . $fileName);
                DB::table('devAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $fileName,
                    'fileExtension' => $img->getClientOriginalExtension(),
                    'filePath' => $filePath,
                    'fileSize' => $size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('Chatsup.chatsup', ["ticket_dev" => $ticketId])
            ->with('success', 'Dev ticket berhasil dibuat.');
    }

    public function validateDevTicket(Request $request)
    {
        $rules = [
            'case_type' => 'required|in:ticket,external',
            'client_ticket_id' => 'required_if:case_type,ticket|nullable|integer|exists:clientTickets,id',
            'role_id' => 'required_if:case_type,external|nullable|integer|exists:devRoles,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp,xlsx,docx,pdf|max:5120',
        ];

        $messages = [
            'case_type.required' => 'Mohon isi jenis kasus yang Anda mau lapor.',
            'client_ticket_id.required_if' => 'Mohon isikan nomor tiket yang dibuat oleh Client.',
            'client_ticket_id.exists' => 'Tiket dipilih tidak diketemui di dalam database atau belum dipilih.',
            'role_id.required_if' => 'Mohon isikan role developer yang akan menangani tiket Anda.',
            'role_id.exists' => 'Mohon isikan role developer yang akan menangani tiket Anda.',
            'title.required' => 'Mohon isi judul tiket tersebut.',
            'title.string' => 'Mohon isi judul yang valid.',
            'title.max' => 'Judul terlalu panjang. Mohon memendekkan judul tersebut.',
            'description.required' => 'Mohon isi deskripsi permasalahan pada tiket tersebut.',
            'description.max' => 'Deskripsi tersebut melewati batasan huruf 10000 karakter.',
            'image.mimes' => 'Gambar tersebut tidak menggunakan format yang dapat di-support.',
            'image.max' => 'Ukuran maksimal gambar harus dibawah 5MB.'
        ];

        $validator = \Validator::make($request->only(array_keys($rules)), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function createDevTicketPage(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(403);

        $selectedId = (int) $request->query('ticket_user');

        $tickets = DB::table('clientTickets')
            ->where('isDeleted', 0)
            ->where('supportID', $user->id)
            ->where('status', 'SEDANG DIPROSES')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'title', 'status']);

        if ($selectedId && !$tickets->contains('id', $selectedId)) {
            $sel = DB::table('clientTickets')
                ->where('id', $selectedId)
                ->where('supportID', $user->id)
                ->select('id', 'title', 'status')
                ->first();
            if ($sel) $tickets = collect([$sel])->concat($tickets);
            else $selectedId = null;
        }

        // Ambil dari model DevRole, alias roleName -> name agar Blade tetap pakai $role->name
        $roles = DevRole::query()
            ->where('isDeleted', 0)
            ->select('id', DB::raw('roleName as name'))
            ->orderBy('roleName')
            ->get();

        return view('Chatsup.devtickets.create', [
            'user' => $user,
            'tickets' => $tickets,
            'selectedId' => $selectedId,
            'roles' => $roles,
        ]);
    }



    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(403);

        // Tickets assigned to this support (all states)
        $tickets = DB::table('clientTickets')
            ->join('clients AS client', 'clientTickets.userID', '=', 'client.id')
            ->where('clientTickets.isDeleted', 0)
            ->where('clientTickets.supportID', $user->id)
            ->whereIn('clientTickets.status', ['BELUM DIPROSES', 'SEDANG DIPROSES', 'SELESAI', 'DITUTUP'])
            ->select(
                'clientTickets.id',
                'clientTickets.title',
                'clientTickets.status',
                'clientTickets.created_at',
                'client.name AS client_name',
                'client.email AS client_email',
                'client.profile_picture AS client_avatar',
                DB::raw('COALESCE((SELECT MAX(created_at) FROM clientChats WHERE clientChats.ticketID = clientTickets.id), clientTickets.created_at) as latest_chat_time')
            )
            ->orderByRaw("CASE WHEN clientTickets.status = 'SEDANG DIPROSES' THEN 0 WHEN clientTickets.status = 'BELUM DIPROSES' THEN 1 WHEN clientTickets.status = 'SELESAI' THEN 2 ELSE 3 END ASC")
            ->orderBy('latest_chat_time', 'DESC')
            ->get();

        if ($tickets->isEmpty()) {
            return redirect()->route('Support.support')->with('error', 'No tickets found.');
        }

        // Determine selected ticket
        $selectedId = (int) $request->get('ticket', $tickets[0]->id);
        $selectedTicket = $tickets->firstWhere('id', $selectedId);

        // If selected ticket isn’t in the list (e.g., filtered), try to load it so UI still works
        if (!$selectedTicket) {
            $sel = DB::table('clientTickets')
                ->join('clients AS client', 'clientTickets.userID', '=', 'client.id')
                ->where('clientTickets.id', $selectedId)
                ->select(
                    'clientTickets.id',
                    'clientTickets.title',
                    'clientTickets.status',
                    'clientTickets.created_at',
                    'client.name AS client_name',
                    'client.email AS client_email',
                    'client.profile_picture AS client_avatar'
                )
                ->first();
            if ($sel) {
                $selectedTicket = $sel;
                $tickets = collect([$sel])->concat($tickets);
            } else {
                $selectedTicket = $tickets->first();
            }
        }

        // Load messages + attachments for selected ticket
        $ticketId = $selectedTicket->id;

        $messages = DB::table('clientChats')
            ->where('ticketID', $ticketId)
            ->orderBy('created_at', 'asc')
            ->select('id', 'type', 'response', 'check', 'created_at')
            ->get();

        $attachments = DB::table('clientAttachments')
            ->whereIn('chatID', $messages->pluck('id'))
            ->get()
            ->groupBy('chatID');

        $selectedTicket->messages = collect();

        $timezone = session('user_timezone', config('app.timezone'));
        foreach ($messages as $msg) {
            $msgAttachments = $attachments->get($msg->id, collect());
            $attList = [];
            foreach ($msgAttachments as $att) {
                $isImage = in_array(strtolower($att->fileExtension), ['jpg', 'jpeg', 'png', 'webp']);
                $normalizedUrl = $isImage ? 'chat_images/' .  $att->fileName
                    : 'chat_attachments/' . $att->fileName;

                $width = null;
                $height = null;
                $displayWidth = null;
                $displayHeight = null;

                if ($isImage) {
                    $imagePath = public_path($normalizedUrl);
                    if (file_exists($imagePath)) {
                        $gambar = new Imagick($imagePath);
                        $width = $gambar->getImageWidth();
                        $height = $gambar->getImageHeight();

                        $maxW = 250;
                        $maxH = 200;
                        $ratio = min($maxW / $width, $maxH / $height, 1);

                        $displayWidth = (int)($width * $ratio);
                        $displayHeight = (int)($height * $ratio);
                        $gambar->clear();
                        $gambar->destroy();
                    }
                }
                $attList[] = [
                    'type' => $isImage ? 'image' : 'file',
                    'name' => $att->fileName,
                    'size' => $att->fileSize,
                    'extension' => $att->fileExtension,
                    'width' => $isImage ? $displayWidth : '',
                    'height' => $isImage ? $displayHeight : ''
                ];
            }

            $cTime = \Carbon\Carbon::parse($msg->created_at)->timezone($timezone);
            $formattedTime = $cTime->isToday() ? $cTime->format('h:i A') : $cTime->format('M j, h:i A');

            $profilePicture = null;

            if ($user->profile_picture) {
                if ($msg->type === 'client')
                    $profilePicture = route('users', ['filename' => $user->profile_picture]);
                if ($msg->type === 'support')
                    $profilePicture = $user->profile_picture;
            }

            $selectedTicket->messages->push([
                'sender' => $msg->type, // 'client' or 'support'
                'avatar' => $profilePicture,
                'message' => $msg->response,
                'status' => $msg->check ?: '',
                'id' => $msg->id,
                'time' => $formattedTime,
                'attachments' => $attList,
            ]);
        }

        return view('Chatsup.supportclientchat', [
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $ticketId = $request->get('ticket');
        $message = $request->input('message');
        $now = now();
        $chatCreated = $now->clone();
        $datePrefix = 'SS-' . \Carbon\Carbon::parse($chatCreated)->format('Ymd');
        $imageResolutions = [];

        if (!DB::table('clientTickets')->where('id', $ticketId)->where('supportID', $userId)) return back();

        if (!$message && !$request->hasFile('attachment') && !$request->hasFile('image')) {
            return back()->with('error', 'Message cannot be empty.');
        }

        $chat = ClientChat::create([
            'ticketID' => $ticketId,
            'type'     => 'support',
            'response' => $message,
            'check'    => 'sent'
        ]);

        $chatId = $chat->id;
        $attCount = 0;

        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment');
            if (!is_array($files)) {
                $files = [$files]; // wrap single file in array
            }

            $folderPath = public_path('chat_attachments');

            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            foreach ($files as $idx => $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'docx') {
                    $rule = new StrictDocx();
                    if (!$rule->passes('attachment.*', $file)) {
                        continue;
                    }
                }
                if ($extension === 'xlsx') {
                    $rule = new StrictXlsx();
                    if (!$rule->passes('attachment.*', $file)) {
                        continue;
                    }
                }
                $imageResolutions[] = [0, 0];
                $attCount++;
                $newFileName = $datePrefix . "-" . $ticketId . '_' . $chatId . '_' . ($idx + 1) . '_' . $file->getClientOriginalName();
                $file->move(public_path('chat_attachments/'), $newFileName);
                $filePath = asset('chat_attachments/' . $newFileName);
                $targetPath = public_path('chat_attachments/' . $newFileName);

                if (file_exists($targetPath)) {
                    $size = filesize($targetPath);
                } else {
                    \Log::error("File not found after move: " . $targetPath);
                    $size = 0; // or handle error
                }

                DB::table('clientAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $newFileName,
                    'filePath' => $filePath,
                    'fileExtension' => $extension,
                    'fileSize' => $size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($request->hasFile('image')) {
            $images = $request->file('image');
            if (!is_array($images)) {
                $images = [$images];
            }

            foreach ($images as $idx => $gambar) {
                $size = $gambar->getSize();
                $attCount++;
                $ext = strtolower($gambar->getClientOriginalExtension());
                $namaGambar = $datePrefix . "-" . $ticketId . '_' . $chatId . '_' . ($idx + 1) . '_' . $gambar->getClientOriginalName();
                try {
                    if (!file_exists($gambar->getRealPath())) {
                        throw new Exception('Uploaded file not found.');
                    }
                    if (filesize($gambar->getRealPath()) > 20 * 1024 * 1024) { // 20MB safety
                        throw new Exception('File too large for Imagick processing.');
                    }

                    $imagick = new Imagick();
                    $imagick->readImage($gambar->getRealPath());

                    if (!empty($imagick->getImageProperties('exif:*'))) {
                        $imagick->stripImage();
                    }

                    $folderPath = public_path('chat_images');
                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0755, true);
                    }

                    $w = $imagick->getImageWidth();
                    $h = $imagick->getImageHeight();
                    $maxDim = 1920;

                    $savePath = $folderPath . '/' . $namaGambar;
                    if ($w > $maxDim || $h > $maxDim) {
                        $ratio = min($maxDim / $w, $maxDim / $h);
                        $imagick->resizeImage((int)($w * $ratio), (int)($h * $ratio), \Imagick::FILTER_LANCZOS, 1);
                    }
                    // Extra downscale kualitas jika masih besar >1.5MB
                    if ($ext === 'jpg' || $ext === 'jpeg') {
                        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $imagick->setImageCompressionQuality(82);
                        if ($size > 1500 * 1024) {
                            $imagick->setImageCompressionQuality(75);
                        }
                    } elseif ($ext === 'webp') {
                        $imagick->setImageCompressionQuality($size > 1500 * 1024 ? 72 : 82);
                    } elseif ($ext === 'png') {
                        $imagick->setOption('png:compression-level', '9');
                        $imagick->setOption('png:compression-filter', '5');
                        if (($w * $h) > (1280 * 1280)) {
                            $imagick->quantizeImage(256, \Imagick::COLORspace_RGB, 0, false, false);
                        }
                    }

                    if (!$imagick->writeImage($savePath)) {
                        throw new Exception('Imagick failed to write image.');
                    }

                    $imageResolutions[] = [$imagick->getImageWidth(), $imagick->getImageHeight()];

                    $imagick->clear();
                    $imagick->destroy();

                    $gambarUrl = asset('chat_images/' . $namaGambar);
                    $finalSize = @filesize($savePath) ?: $size;
                } catch (Exception $e) {
                    dd('Imagick error: ' . $e->getMessage());
                }

                DB::table('clientAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $namaGambar,
                    'filePath' => $gambarUrl,
                    'fileSize' => $finalSize,
                    'fileExtension' => $gambar->getClientOriginalExtension(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->pushChatToFirebase($chat, $imageResolutions);
        $tickClass = ClientTicket::findOrFail($ticketId);
        $user = Client::findOrFail($tickClass->userID);

        //Email Notification
        Mail::to($user->email)->send(new NotificationMail($user->name, $tickClass->title, $ticketId, $message, $attCount));

        // WhatsApp notification: support -> client
        /*try {
            $ticket = DB::table('clientTickets')
                ->join('clients as c','clientTickets.userID','=','c.id')
                ->select('clientTickets.id','clientTickets.title','clientTickets.status','c.country_code as country_code','c.phone_number as client_phone','c.name as client_name')
                ->where('clientTickets.id',$ticketId)->first();
            if ($ticket && $ticket->client_phone) {
                app(WhatsAppNotificationService::class)->sendChatNotification([
                    'to' => $ticket->country_code . $this->formatPhone($ticket->client_phone),
                    'sender' => Auth::user()->name ?? 'Support',
                    'message' => mb_substr($message ?? '(attachment)',0,500),
                    'ticketId' => $ticket->id,
                    'ticketTitle' => $ticket->title,
                    'status' => $ticket->status,
                    'chatType' => 'support_to_client'
                ]);
            }
        } catch (\Throwable $e) {}*/

        return response()->json([
            'success' => true,
            'chat'    => $chat
        ]);
    }

    private function pushChatToFirebase(ClientChat $chat, $imageResolutions)
    {
        //Database URI is used since this machine is using an old version of firebase and always defaults to the old link.
        $factory = (new Factory)->withServiceAccount(base_path(config('firebase.credentials.file')))->withDatabaseUri(config('firebase.credentials.url'));
        $db = $factory->createDatabase();

        $ticketId = $chat->ticketID;
        $attachments = $chat->attachments->values()->map(function ($a, $index) use ($imageResolutions) {
            $url = 'chat_attachments/';
            $isImage = in_array(strtolower($a->fileExtension), ['jpg', 'jpeg', 'png', 'webp']);
            if ($isImage) {
                $url = 'chat_images/';
            }

            return [
                'name' => $a->fileName,
                'extension' => $a->fileExtension,
                'url' => $a->filePath, //for now. find a way to put in storage.
                'size' => $a->fileSize,
                'resolution' => $imageResolutions[$index] ?? [0, 0]
            ];
        });

        $db->getReference("tickets/{$ticketId}/{$chat->id}")
            ->set([
                'id' => $chat->id,
                'response' => $chat->response,
                'type' => $chat->type,
                'created_at' => $chat->created_at->toDateTimeString(),
                'attachments' => $attachments,
                'ticket_id' => $chat->ticketID
            ]);

        //Immediately remove chat cuz we just need it to store the real time thing.
        $db->getReference("tickets/{$ticketId}/{$chat->id}")->remove();
    }

    public function resolveTicket(Request $request, $id)
    {
        $ticket = ClientTicket::findOrFail($id);
        if ($ticket->status === "DITUTUP" || $ticket->supportID !== Auth::id()) {
            return back();
        }

        $ticket->status = "SELESAI";
        $ticket->save();

        $user = Client::findOrFail($ticket->userID);

        //Email Notification
        Mail::to($user->email)->send(new CompleteMail($user->name, $ticket->title, $id));
        $this->changeToResolveToFirebase($ticket);
        return back();
    }

    private function changeToResolveToFirebase(ClientTicket $ticket)
    {
        $factory = (new Factory)->withServiceAccount(base_path(config('firebase.credentials.file')))->withDatabaseUri(config('firebase.credentials.url'));
        $db = $factory->createDatabase();

        $db->getReference("tickets/status/resolved/{$ticket->id}")->set([
            'id' => $ticket->id,
            'status' => 'SELESAI'
        ]);

        $db->getReference("tickets/status/resolved/{$ticket->id}")->remove();
    }

    private function readWithFirebase($id)
    {
        $idString = implode(',', $this->getIDsToRead($id));

        $factory = (new Factory)->withServiceAccount(base_path(config('firebase.credentials.file')))->withDatabaseUri(config('firebase.credentials.url'));
        $db = $factory->createDatabase();

        $db->getReference("tickets/status/reader/sp-{$id}")->set([
            "arrayReading" => $idString
        ]);

        $db->getReference("tickets/status/reader/sp-{$id}")->remove();
    }

    public function readIDinRealtime(Request $request, ClientTicket $t)
    {
        $request->validate([
            'ids' => ['required', 'regex:/^\d+(,\d+)*$/'],
        ]);

        $ids = $request->ids;
        $readIds = explode(',', $ids);

        ClientChat::where('ticketID', $t->id)->whereIn('id', $readIds)->update(['check' => 'read']);

        return response()->json([
            'read' => $ids
        ]);
    }

    private function getIDsToRead($id)
    {
        $ticket = ClientTicket::findOrFail($id);
        $timezone = session('user_timezone', config('app.timezone'));

        $latestChat = ClientChat::where('ticketID', $ticket->id)->where('type', 'client')->latest('created_at')->first();

        if (!$latestChat) {
            return [];
        }

        $dayStart = Carbon::parse($latestChat->created_at)->timezone($timezone)->startOfDay();

        $ids = ClientChat::where('ticketID', $ticket->id)
            ->where('type', 'client')
            ->whereBetween('created_at', [
                $dayStart->copy()->timezone(config('app.timezone')),
                $dayStart->copy()->endOfDay()->timezone(config('app.timezone'))
            ])
            ->pluck('id')
            ->toArray();

        return $ids;
    }
}
