<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DevTicket;
use App\Models\DevChat;
use App\Models\User;
use App\Rules\StrictDocx;
use App\Rules\StrictExe;
use App\Rules\StrictXlsx;
use Illuminate\Support\Facades\Log;
//use App\Services\WhatsAppNotificationService;

use Imagick;

class ChatDevController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id(); // Ambil user id dari session
        $user = Auth::user(); // Ambil user object dari session

        // Mengambil semua tiket yang terkait dengan developer
        $tickets = DB::table('devTickets')
            ->join('users AS support', 'devTickets.supportID', '=', 'support.id')
            ->join('users AS developer', 'devTickets.devID', '=', 'developer.id')
            ->where('devTickets.devID', '=', $userId)
            ->whereIn('devTickets.status', ['In Progress', 'Resolved'])
            ->where('devTickets.isDeleted', '=', 0)
            ->select(
                'devTickets.id',
                'devTickets.title',
                'devTickets.status',
                'devTickets.created_at',
                'support.name AS support_name',
                'support.profile_picture AS support_avatar',
                'support.email AS support_email',
                'developer.email AS developer_email',
                'developer.name AS developer_name',
                'developer.profile_picture AS developer_avatar',
                'devTickets.priority',
                DB::raw('COALESCE((SELECT MAX(created_at) FROM devChats WHERE devChats.ticket_id = devTickets.id), devTickets.created_at) as latest_chat_time')
            )
            ->orderByRaw("CASE devTickets.status WHEN 'In Progress' THEN 0 ELSE 1 END ASC")
            ->orderByRaw('CASE WHEN devTickets.priority IS NULL THEN 1 ELSE 0 END, devTickets.priority ASC')
            ->orderBy('latest_chat_time', 'DESC')
            ->get();

        // Check if any tickets were returned
        if ($tickets->isEmpty()) {
            return redirect()->route('Developer.developer')->with('error', 'No tickets found.');
        }

        // Tentukan ticket ID yang pertama kali dipilih (jika tidak ada, pilih ticket pertama)
        $ticketId = $request->get('ticket', $tickets[0]->id);

        // Mengambil pesan-pesan terkait tiket yang dipilih
        $messages = DB::table('devChats')
            ->join('devTickets', 'devChats.ticket_id', '=', 'devTickets.id')
            ->where('devTickets.id', '=', $ticketId)
            ->select(
                'devChats.sender',
                'devChats.response AS chat_message',
                'devChats.created_at AS chat_time',
                'devChats.check',
                'devChats.id'
            )
            ->orderBy('devChats.created_at', 'asc')
            ->get();

        $attachments = DB::table('devAttachments')
            ->whereIn('chatID', $messages->pluck('id'))
            ->get()
            ->groupBy('chatID');

        // Menyiapkan data untuk UI
        $selectedTicket = $tickets->firstWhere('id', $ticketId);

        if (!$selectedTicket) abort(404);

        // Inisialisasi messages sebagai objek (bukan array)
        $selectedTicket->messages = collect(); // Menginisialisasi sebagai Collection

        // Menambahkan pesan chat ke data
        foreach ($messages as $msg) {
            $msgAttachments = $attachments->get($msg->id, collect());
            $attList = [];
            foreach ($msgAttachments as $att) {
                $isImage = in_array(strtolower($att->fileExtension), ['jpg', 'jpeg', 'png', 'webp']);

                if ($isImage) {
                    $normalizedUrl = 'chat_images/' .  $att->fileName;
                } else {
                    $normalizedUrl = 'chat_attachments/' . $att->fileName;
                }
                $displayName = $att->fileName;

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

                if ($isImage) {
                    $attList[] = [
                        'type' => 'image',
                        'url' => $normalizedUrl,
                        'name' => $displayName,
                        'size' => $att->fileSize,
                        'width' => $displayWidth,
                        'height' => $displayHeight
                    ];
                } else {
                    $attList[] = [
                        'type' => 'file',
                        'url' => $normalizedUrl,
                        'name' => $displayName,
                        'size' => $att->fileSize
                    ];
                }
            }

            $timezone = session('user_timezone', config('app.timezone'));
            $cTime = Carbon::parse($msg->chat_time)->timezone($timezone);

            if ($cTime->isToday()) {
                $formattedTime = $cTime->format('h:i A');
            } else {
                $formattedTime = $cTime->format('M j, h:i A');
            }

            $status = "";

            if ($msg->check) $status = $msg->check;

            $selectedTicket->messages->push([
                'sender' => $msg->sender,
                'avatar' => $msg->sender === 'support' ? $selectedTicket->support_avatar : $selectedTicket->developer_avatar,
                'message' => $msg->chat_message,
                'status' => $status,
                'id' => $msg->id,
                'time' => $formattedTime,
                'attachments' => $attList,
            ]);
        }
        $readIds = $this->getIDsToRead($selectedTicket->id);
        DevChat::where('ticket_id', $selectedTicket->id)->whereIn('id', $readIds)->update(['check' => 'read']);
        return view('Chatdev.chatdev', compact('selectedTicket', 'tickets', 'user'), ["readChats" => implode(',', $readIds)]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id(); // Ambil user id dari session login
        $ticketId = $request->get('ticket'); // Mengambil ticket ID dari request
        $message = $request->input('message'); // Pesan yang dikirim
        $chatCreated = DB::table('devTickets')->where('id', $ticketId)->value('created_at');
        $datePrefix = 'TK-' . \Carbon\Carbon::parse($chatCreated)->format('Ymd');

        if (!DB::table('devTickets')->where('id', $ticketId)->where('devID', $userId)) return back();

        // Validasi input message
        if (in_array(DB::table('devTickets')->where('id', $ticketId)->value('status'), ['Closed', 'Resolved'])) {
            return back()->with('error', 'Tiket sudah Resolved. Klik Reopen untuk melanjutkan chat.');
        }

        if (!$message && !$request->hasFile('attachment') && !$request->hasFile('image')) {
            return back()->with('error', 'Message cannot be empty.');
        }

        // Menyimpan pesan ke database
        $chatId = DB::table('devChats')->insertGetId([
            'ticket_id' => $ticketId,
            'sender' => 'dev', // 'support' bisa diganti sesuai dengan role yang sedang mengirim
            'response' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Menangani file upload jika ada
        if ($request->hasFile('attachment')) {
            // Hitung total attachment yang sudah ada untuk tiket ini
            $attachmentCount = DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['docx', 'pdf', 'xlsx', 'exe'])
                ->count();

            foreach ($request->file('attachment') as $idx => $file) {
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
                if ($extension === 'exe') {
                    $rule = new StrictExe();
                    if (!$rule->passes('attachment.*', $file)) {
                        continue;
                    }
                }
                $size = $file->getSize();
                $newFileName = $datePrefix . "-" . $ticketId . '_' . $chatId . '_' . 'dev_' . ($attachmentCount + $idx + 1) . '_' . $file->getClientOriginalName();
                //$filePath = $file->storeAs('chat_attachments', $newFileName, 'public');
                $targetPath = public_path('chat_attachments/' . $newFileName);

                if (!file_exists($targetPath)) {
                    if (!is_dir(public_path('chat_attachments'))) {
                        mkdir(public_path('chat_attachments'), 0775, true);
                    }
                }
                $file->move(public_path('chat_attachments/'), $newFileName);
                $filePath = asset('chat_attachments/' . $newFileName);

                DB::table('devAttachments')->insert([
                    'chatID' => $chatId,
                    'fileName' => $newFileName,
                    'filePath' => $filePath,
                    'fileExtension' => $file->getClientOriginalExtension(),
                    'fileSize' => $size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($request->hasFile('image')) {
            // Hitung total image yang sudah ada untuk tiket ini
            $imageCount = DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['jpg', 'jpeg', 'png', 'webp'])
                ->count();

            foreach ($request->file('image') as $idx => $gambar) {
                $size = $gambar->getSize();
                $ext = strtolower($gambar->getClientOriginalExtension());
                $namaGambar = $datePrefix . "-" . $ticketId . '_' . $chatId . '_' . 'dev_' . ($imageCount + $idx + 1) . '_' . $gambar->getClientOriginalName();
                $targetPath = public_path('chat_images/' . $namaGambar);

                if (!file_exists($targetPath)) {
                    if (!is_dir(public_path('chat_images'))) {
                        mkdir(public_path('chat_images'), 0775, true);
                    }
                }

                $imagick = new Imagick();
                $imagick->readImage($gambar->getRealPath());
                $imagick->stripImage();

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
                } elseif ($ext === 'webp') {
                    $imagick->setImageCompressionQuality(82);
                } elseif ($ext === 'png') {
                    $imagick->setOption('png:compression-level', '9');
                    $imagick->setOption('png:compression-filter', '5');
                    if (($w * $h) > (1280 * 1280)) {
                        $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                    }
                }

                if (!$imagick->writeImage($targetPath)) {
                    throw new Exception('Imagick failed to write image.');
                }

                $imagick->clear();
                $imagick->destroy();
                //$gambar->move(public_path('chat_images/'), $namaGambar);   
                $gambarUrl = asset('chat_images/' . $namaGambar);
                $finalSize = @filesize($targetPath) ?: $size;

                DB::table('devAttachments')->insert([
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
        // Kirim notifikasi WhatsApp ke support (jika nomor tersedia)
        /*try {
            $ticket = DB::table('devTickets')
                ->join('users as support', 'devTickets.supportID', '=', 'support.id')
                ->select('devTickets.id','devTickets.title','support.phone_number as support_phone','support.name as support_name')
                ->where('devTickets.id', $ticketId)->first();
            if ($ticket && $ticket->support_phone) {
                app(WhatsAppNotificationService::class)->sendChatNotification([
                    'to' => '62' . $this->formatPhone($ticket->support_phone),
                    'sender' => Auth::user()->name ?? 'Developer',
                    'message' => mb_substr($message ?? '(attachment)',0,500),
                    'ticketId' => $ticket->id,
                    'ticketTitle' => $ticket->title,
                    'chatType' => 'dev_to_support'
                ]);
            }
        } catch (\Throwable $e) {
            // swallow – do not break UX
        }*/
        // Redirect kembali ke halaman tiket dengan pesan sukses
        return redirect()->route('Chatdev.chatdev', ['ticket' => $ticketId])
            ->with('success', 'Message sent successfully.');
    }

    public function storeRealtime(Request $request)
    {
        $userId = Auth::id();
        $ticketId = $request->get('ticket');
        $message = $request->input('message');

        // Validasi status tiket
        $ticket = DB::table('devTickets')->where('id', $ticketId)->first();
        if (!$ticket || $ticket->status !== 'In Progress') {
            return response()->json(['error' => 'Tiket belum diambil oleh Developer atau ditutup'], 403);
        }

        if (!DB::table('devTickets')->where('id', $ticketId)->where('devID', $userId)) return response()->json(['error' => 'Tiket tidak diotorisasi'], 403);

        // Validate request
        if (!$message && !$request->hasFile('attachment') && !$request->hasFile('image')) {
            foreach ($request->file('attachment', []) as $file) {
                Log::error('Upload debug', [
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage(),
                    'size' => $file->getSize(),
                ]);
            }

            Log::error('Runtime limits', [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ]);

            // Log::error('Failed to store message', [
            //     'exception' => $request->all(),
            //     'ticket_id' => $ticketId ?? null,
            //     'user_id' => auth()->id(),
            // ]);
            return response()->json(['error' => 'Input tidak bisa kosong.'], 400);
        }
        $now = now();
        $chatCreated = $now->clone();
        $datePrefix = 'TK-' . \Carbon\Carbon::parse($chatCreated)->format('Ymd');

        $allAttachments = [];

        try {
            // Store message in database
            $chatId = DB::table('devChats')->insertGetId([
                'ticket_id' => $ticketId,
                'sender' => 'dev',
                'response' => $message,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Handle attachments if any
            if ($request->hasFile('attachment')) {
                foreach ($request->file('attachment') as $idx => $file) {
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
                    if ($extension === 'exe') {
                        $rule = new StrictExe();
                        if (!$rule->passes('attachment.*', $file)) {
                            continue;
                        }
                    }
                    $attachmentCount = DB::table('devAttachments')
                        ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                        ->where('devChats.ticket_id', $ticketId)
                        ->whereIn('devAttachments.fileExtension', ['docx', 'pdf', 'xlsx'])
                        ->count();
                    $size = $file->getSize();
                    // For now, we'll store the attachment info
                    // In a real implementation, you'd want to handle file uploads properly
                    $newFileName = $datePrefix . "-" . $ticketId . '_' .  $chatId . '_' . 'dev_' . ($attachmentCount + $idx + 1) . '_' . $file->getClientOriginalName();
                    $targetPath = public_path('chat_attachments/' . $newFileName);

                    if (!file_exists($targetPath)) {
                        if (!is_dir(public_path('chat_attachments'))) {
                            mkdir(public_path('chat_attachments'), 0775, true);
                        }
                    }
                    $file->move(public_path('chat_attachments/'), $newFileName);
                    $filePath = asset('chat_attachments/' . $newFileName);

                    DB::table('devAttachments')->insert([
                        'chatID' => $chatId,
                        'fileName' => $newFileName ?? 'attachment',
                        'filePath' => $filePath ?? '',
                        'fileSize' => $size,
                        'fileExtension' => $file->getClientOriginalExtension() ?? 'unknown',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $allAttachments[] = [
                        'type' => 'file',
                        'name' => $newFileName,
                        'url' => $filePath,
                        'size' => $size,
                        'extension' => $file->getClientOriginalExtension(),
                    ];
                }
            }

            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $idx => $gambar) {
                    $imageCount = DB::table('devAttachments')
                        ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                        ->where('devChats.ticket_id', $ticketId)
                        ->whereIn('devAttachments.fileExtension', ['jpg', 'jpeg', 'png', 'webp'])
                        ->count();

                    $size = $gambar->getSize();
                    $ext = strtolower($gambar->getClientOriginalExtension());

                    $namaGambar = $datePrefix . "-" . $ticketId . '_' .  $chatId . '_' . 'dev_' . ($imageCount + $idx + 1) . '_' . $gambar->getClientOriginalName();
                    $targetPath = public_path('chat_images/' . $namaGambar);

                    if (!file_exists($targetPath)) {
                        if (!is_dir(public_path('chat_images'))) {
                            mkdir(public_path('chat_images'), 0775, true);
                        }
                    }
                    $imagick = new Imagick();
                    $imagick->readImage($gambar->getRealPath());
                    $imagick->stripImage();

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
                    } elseif ($ext === 'webp') {
                        $imagick->setImageCompressionQuality(82);
                    } elseif ($ext === 'png') {
                        $imagick->setOption('png:compression-level', '9');
                        $imagick->setOption('png:compression-filter', '5');
                        if (($w * $h) > (1280 * 1280)) {
                            $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                        }
                    }

                    if (!$imagick->writeImage($targetPath)) {
                        throw new Exception('Imagick failed to write image.');
                    }

                    $imagick->clear();
                    $imagick->destroy();
                    //$gambar->move(public_path('chat_images/'), $namaGambar);   
                    $gambarUrl = asset('chat_images/' . $namaGambar);
                    $finalSize = @filesize($targetPath) ?: $size;

                    DB::table('devAttachments')->insert([
                        'chatID' => $chatId,
                        'fileName' => $namaGambar,
                        'filePath' => $gambarUrl,
                        'fileSize' => $finalSize,
                        'fileExtension' => $gambar->getClientOriginalExtension(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $allAttachments[] = [
                        'type' => 'image',
                        'name' => $namaGambar,
                        'url' => $gambarUrl,
                        'size' => $finalSize,
                        'extension' => $gambar->getClientOriginalExtension(),
                    ];
                }
            }

            // WhatsApp notification (dev -> support)
            /*try {
                $ticket = DB::table('devTickets')
                    ->join('users as support', 'devTickets.supportID', '=', 'support.id')
                    ->select('devTickets.id','devTickets.title','support.phone_number as support_phone','support.name as support_name')
                    ->where('devTickets.id', $ticketId)->first();
                if ($ticket && $ticket->support_phone) {
                    app(WhatsAppNotificationService::class)->sendChatNotification([
                        'to' => '62' . $this->formatPhone($ticket->support_phone),
                        'sender' => Auth::user()->name ?? 'Developer',
                        'message' => mb_substr($message ?? '(attachment)',0,500),
                        'ticketId' => $ticket->id,
                        'ticketTitle' => $ticket->title,
                        'chatType' => 'dev_to_support'
                    ]);
                }
            } catch (\Throwable $e) {}*/

            $timezone = session('user_timezone', config('app.timezone'));
            $cTime = $now->clone()->timezone($timezone);

            return response()->json([
                'success' => true,
                'message' => $message,
                'chat_id' => $chatId,
                'attachments' => $allAttachments,
                'createdAt' => $cTime,
                'sender' => 'dev',
                'senderId' => $userId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store message', [
                'exception' => $e,
                'ticket_id' => $ticketId ?? null,
                'chat_id' => $chatId ?? null,
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to store message: ' . $e->getMessage()], 500);
        }
    }

    public function resolveTicket(Request $request, $id)
    {
        $ticket = DevTicket::findOrFail($id);
        if ($ticket->devID !== Auth::id()) return back();
        if (strtolower($ticket->status) === "closed") return back();
        $ticket->status = "Resolved";
        $ticket->save();
        $supportData = User::where('id', $ticket->supportID)->first();
        $supportNumber = $supportData->phone_number;
        send_notif_wa($supportNumber, 'Tiket *' . $ticket->title . '* telah diselesaikan oleh developer: *' . Auth::user()->name . '*');
        return back();
    }

    public function undoTicket(Request $request, $id)
    {
        $ticket = DevTicket::findOrfail($id);
        if ($ticket->devID !== Auth::id()) {
            return back();
        }
        if (strtolower($ticket->status) === "closed") {
            return back();
        }
        $ticket->status = "Open";
        $ticket->devID = null;
        $ticket->save();
        return redirect()->route('Chatdev.chatdev');
    }

    public function reopenTicket($id)
    {
        try {
            $ticket = DevTicket::findOrFail($id);
            if ($ticket->devID !== Auth::id()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membuka tiket ini.');
            }
            if ($ticket->status !== 'Resolved') {
                return redirect()->back()->with('error', 'Tiket hanya bisa dibuka kembali jika statusnya Resolved.');
            }
            $ticket->status = 'In Progress';
            $ticket->save();
            DB::table('devChats')->insert([
                'ticket_id' => $ticket->id,
                'sender' => 'system',
                'response' => 'Tiket dibuka kembali oleh ' . Auth::user()->name . ' (' . Auth::user()->email . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $supportData = User::where('id', $ticket->supportID)->first();
            $supportNumber = $supportData->phone_number;
            send_notif_wa($supportNumber, 'Tiket *' . $ticket->title . '* telah dibuka kembali oleh developer: *' . Auth::user()->name . '*');
            return redirect()->back()->with('success', 'Tiket berhasil dibuka kembali.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getIDsToRead($id)
    {
        $ticket = DevTicket::findOrFail($id);
        $timezone = session('user_timezone', config('app.timezone'));

        $latestChat = DevChat::where('ticket_id', $ticket->id)->where('sender', 'support')->latest('created_at')->first();

        if (!$latestChat) {
            return [];
        }

        $dayStart = Carbon::parse($latestChat->created_at)->timezone($timezone)->startOfDay();

        $ids = DevChat::where('ticket_id', $ticket->id)
            ->where('sender', 'support')
            ->whereBetween('created_at', [
                $dayStart->copy()->timezone(config('app.timezone')),
                $dayStart->copy()->endOfDay()->timezone(config('app.timezone'))
            ])
            ->pluck('id')
            ->toArray();

        return $ids;
    }

    public function readIDinRealtime(Request $request, DevTicket $t)
    {
        $request->validate([
            'ids' => ['required', 'regex:/^\d+(,\d+)*$/'],
        ]);

        $ids = $request->ids;
        $readIds = explode(',', $ids);

        DevChat::where('ticket_id', $t->id)->whereIn('id', $readIds)->update(['check' => 'read']);

        return response()->json([
            'read' => $ids
        ]);
    }

    private function formatPhone(?string $raw): ?string
    {
        if (!$raw) return null;
        $clean = preg_replace('/[^0-9]/', '', $raw);
        $clean = ltrim($clean, '0');
        return $clean;
    }
}
