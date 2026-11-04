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
use App\Rules\StrictXlsx;
//use App\Services\WhatsAppNotificationService;

use Imagick;

class SupportChatController extends Controller
{
    public function index(Request $request)
    {
        $isHistory = $request->boolean('history');

        // FIX: gunakan DevTicket + kolom yang sesuai
        $query = DevTicket::select(
            'devTickets.*',
            'users.name as developer_name',
            'users.email as developer_email',
            DB::raw('COALESCE((SELECT MAX(created_at) FROM devChats WHERE devChats.ticket_id = devTickets.id), devTickets.created_at) as latest_chat_time')
        )
            ->leftJoin('users', 'users.id', '=', 'devTickets.devID')
            ->where('devTickets.supportID', auth()->id())
            ->where('devTickets.isDeleted', 0)
            ->orderByRaw("CASE devTickets.status WHEN 'In Progress' THEN 0 WHEN 'Open' THEN 1 WHEN 'Resolved' THEN 2 ELSE 3 END ASC")
            ->orderBy('latest_chat_time', 'DESC');

        $tickets = $isHistory
            ? $query->where('devTickets.status', 'Closed')->get()
            : $query->whereIn('devTickets.status', ['Open', 'In Progress', 'Resolved'])->get();

        if ($tickets->isEmpty()) {
            return redirect()->route('Support.support')->with('error', 'No tickets found.');
        }

        // Pastikan selected ticket berasal dari daftar yang sudah difilter
        $selectedId = (int) $request->get('ticket_dev');
        $selectedTicket = $tickets->firstWhere('id', $selectedId) ?? $tickets->first();

        // Gunakan ID dari selectedTicket untuk load pesan
        $ticketId = $selectedTicket->id;

        $messages = DB::table('devChats')
            ->join('devTickets', 'devChats.ticket_id', '=', 'devTickets.id')
            ->where('devTickets.id', '=', $ticketId)
            ->where('devTickets.isDeleted', '=', 0)
            ->select(
                'devChats.sender',
                'devChats.check',
                'devChats.id',
                'devChats.response AS chat_message',
                'devChats.created_at AS chat_time'
            )
            ->orderBy('devChats.created_at', 'asc')
            ->get();

        $attachments = DB::table('devAttachments')
            ->whereIn('chatID', $messages->pluck('id'))
            ->get()
            ->groupBy('chatID');

        if (!$selectedTicket) abort(404);

        $selectedTicket->messages = collect();

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

                $attList[] = $isImage
                    ? ['type' => 'image', 'url' => $normalizedUrl, 'name' => $att->fileName, 'size' => $att->fileSize, 'width' => $displayWidth, 'height' => $displayHeight]
                    : ['type' => 'file',  'url' => $normalizedUrl, 'name' => $att->fileName, 'size' => $att->fileSize];
            }

            $timezone = session('user_timezone', config('app.timezone'));
            $cTime = Carbon::parse($msg->chat_time)->timezone($timezone);
            $formattedTime = $cTime->isToday() ? $cTime->format('h:i A') : $cTime->format('M j, h:i A');

            $status = $msg->check ?: "";

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

        // NEW: kirim ke view
        $readChats = implode(',', $readIds);

        return view('Chatsup.chatsup', [
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket,
            'readChats' => $readChats,
            'user' => Auth::user(),
            'isHistory' => $isHistory, // <-- add
        ]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id(); // Sesuaikan dengan Auth::id() jika diperlukan
        $ticketId = $request->get('ticket'); // Mengambil ticket ID dari request
        $message = $request->input('message'); // Pesan yang dikirim

        if (!DB::table('devTickets')->where('id', $ticketId)->where('supportID', $userId)) return back();

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
            'sender' => 'support', // 'support' bisa diganti sesuai dengan role yang sedang mengirim
            'response' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $now = now();
        $chatCreated = $now->clone();
        $datePrefix = 'TK-' . \Carbon\Carbon::parse($chatCreated)->format('Ymd');

        // Menangani file upload jika ada
        if ($request->hasFile('attachment')) {
            // Hitung total attachment yang sudah ada untuk tiket ini
            $attachmentCount = DB::table('devAttachments')
                ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                ->where('devChats.ticket_id', $ticketId)
                ->whereIn('devAttachments.fileExtension', ['docx', 'pdf', 'xlsx'])
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
                $size = $file->getSize();
                $newFileName = $datePrefix . '-' . ($attachmentCount + $idx + 1) . '_' . $file->getClientOriginalName();
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
                    'fileSize' => $size,
                    'fileExtension' => $file->getClientOriginalExtension(),
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
                $ext  = strtolower($gambar->getClientOriginalExtension());

                $namaGambar = $datePrefix . '-' . ($imageCount + $idx + 1) . '_' . $gambar->getClientOriginalName();
                $targetPath = public_path('chat_images/' . $namaGambar);

                if (!file_exists($targetPath)) {
                    if (!is_dir(public_path('chat_images'))) {
                        mkdir(public_path('chat_images'), 0775, true);
                    }
                }

                $imagick = new Imagick();
                $imagick->readImage($gambar->getRealPath());
                $imagick->stripImage();

                // Resize jika besar (dimensi > 1920) atau file > 2MB
                $w = $imagick->getImageWidth();
                $h = $imagick->getImageHeight();
                $maxDim = 1920;
                if ($w > $maxDim || $h > $maxDim) {
                    $ratio = min($maxDim / $w, $maxDim / $h);
                    $imagick->resizeImage((int)($w * $ratio), (int)($h * $ratio), \Imagick::FILTER_LANCZOS, 1);
                }

                // Kompresi berdasarkan tipe
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageCompressionQuality(82); // ~80-85 kualitas baik
                } elseif ($ext === 'webp') {
                    $imagick->setImageCompressionQuality(82);
                } elseif ($ext === 'png') {
                    // Kompresi PNG (lossless-ish)
                    $imagick->setOption('png:compression-level', '9');
                    $imagick->setOption('png:compression-filter', '5');
                    // Kurangi palette bila sangat besar
                    if (($w * $h) > (1280 * 1280)) {
                        $imagick->quantizeImage(256, \Imagick::COLORSPACE_RGB, 0, false, false);
                    }
                }

                if (!$imagick->writeImage($targetPath)) {
                    throw new \Exception('Imagick failed to write image.');
                }

                $imagick->clear();
                $imagick->destroy();

                $gambarUrl  = asset('chat_images/' . $namaGambar);
                $finalSize  = @filesize($targetPath) ?: $size; // catat ukuran final setelah kompresi

                DB::table('devAttachments')->insert([
                    'chatID'       => $chatId,
                    'fileName'     => $namaGambar,
                    'filePath'     => $gambarUrl,
                    'fileSize'     => $finalSize,
                    'fileExtension' => $gambar->getClientOriginalExtension(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // WhatsApp notification (support -> developer)
        /*try {
            $ticket = DB::table('devTickets')
                ->join('users as dev', 'devTickets.devID', '=', 'dev.id')
                ->join('users as support', 'devTickets.supportID', '=', 'support.id')
                ->select('devTickets.id','devTickets.title','dev.phone_number as dev_phone','dev.name as dev_name','support.name as support_name')
                ->where('devTickets.id', $ticketId)->first();
            if ($ticket && $ticket->dev_phone) {
                app(WhatsAppNotificationService::class)->sendChatNotification([
                    'to' => '62' . $this->formatPhone($ticket->dev_phone),
                    'sender' => $ticket->support_name ?? 'Support',
                    'message' => mb_substr($message ?? '(attachment)',0,500),
                    'ticketId' => $ticket->id,
                    'ticketTitle' => $ticket->title,
                    'chatType' => 'support_to_dev'
                ]);
            }
        } catch (\Throwable $e) {}*/

        // Redirect kembali ke halaman tiket dengan pesan sukses
        return redirect()->route('Chatsup.chatsup', parameters: ['ticket_dev' => $ticketId])
            ->with('success', 'Message sent successfully.');
    }

    private function getIDsToRead($id)
    {
        $ticket = DevTicket::findOrFail($id);
        $timezone = session('user_timezone', config('app.timezone'));

        $latestChat = DevChat::where('ticket_id', $ticket->id)->where('sender', 'dev')->latest('created_at')->first();

        if (!$latestChat) {
            return [];
        }

        $dayStart = Carbon::parse($latestChat->created_at)->timezone($timezone)->startOfDay();

        $ids = DevChat::where('ticket_id', $ticket->id)
            ->where('sender', 'dev')
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

    public function storeRealtime(Request $request)
    {
        $userId = Auth::id();
        $ticketId = $request->get('ticket');
        $message = $request->input('message');

        if (!DB::table('devTickets')->where('id', $ticketId)->where('supportID', $userId)) return back();

        // Validasi status tiket
        $ticket = DB::table('devTickets')->where('id', $ticketId)->first();
        if (!$ticket || in_array($ticket->status, ['Closed', 'Resolved'])) {
            return response()->json(['error' => 'Tiket sudah Resolved atau ditutup'], 403);
        }

        // Validate request
        if (!$message && !$request->hasFile('attachment') && !$request->hasFile('image')) {
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
                'sender' => 'support',
                'response' => $message,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Handle attachments if any
            if ($request->hasFile('attachment')) {
                foreach ($request->file('attachment') as $idx => $file) {
                    $attachmentCount = DB::table('devAttachments')
                        ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                        ->where('devChats.ticket_id', $ticketId)
                        ->whereIn('devAttachments.fileExtension', ['docx', 'pdf', 'xlsx'])
                        ->count();
                    // For now, we'll store the attachment info
                    // In a real implementation, you'd want to handle file uploads properly
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
                    $size = $file->getSize();
                    $newFileName =  $datePrefix . '-' . $chatId . '_' . ($attachmentCount + $idx + 1) . '_' . $file->getClientOriginalName();
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
                        'fileSize' => $size,
                        'fileExtension' => $extension,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $allAttachments[] = [
                        'type' => 'file',
                        'name' => $newFileName,
                        'url' => $filePath,
                        'extension' => $extension,
                        'size' => $size
                    ];
                }
            }

            if ($request->hasFile('image')) {
                $imageCount = DB::table('devAttachments')
                    ->join('devChats', 'devAttachments.chatID', '=', 'devChats.id')
                    ->where('devChats.ticket_id', $ticketId)
                    ->whereIn('devAttachments.fileExtension', ['jpg', 'jpeg', 'png', 'webp'])
                    ->count();

                foreach ($request->file('image') as $idx => $gambar) {
                    $size = $gambar->getSize();
                    $ext  = strtolower($gambar->getClientOriginalExtension());

                    $namaGambar = $datePrefix . '-' . $chatId . '_' . ($imageCount + $idx + 1) . '_' . $gambar->getClientOriginalName();
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
                        throw new \Exception('Imagick failed to write image.');
                    }

                    $imagick->clear();
                    $imagick->destroy();

                    $gambarUrl  = asset('chat_images/' . $namaGambar);
                    $finalSize  = @filesize($targetPath) ?: $size;

                    DB::table('devAttachments')->insert([
                        'chatID'        => $chatId,
                        'fileName'      => $namaGambar,
                        'filePath'      => $gambarUrl,
                        'fileSize'      => $finalSize,
                        'fileExtension' => $gambar->getClientOriginalExtension(),
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]);

                    $allAttachments[] = [
                        'type'      => 'image',
                        'name'      => $namaGambar,
                        'size'      => $finalSize,
                        'url'       => $gambarUrl,
                        'extension' => $gambar->getClientOriginalExtension(),
                    ];
                }
            }

            // WhatsApp notification (support -> developer)
            /*try {
                $ticket = DB::table('devTickets')
                    ->join('users as dev', 'devTickets.devID', '=', 'dev.id')
                    ->join('users as support', 'devTickets.supportID', '=', 'support.id')
                    ->select('devTickets.id','devTickets.title','dev.phone_number as dev_phone','dev.name as dev_name','support.name as support_name')
                    ->where('devTickets.id', $ticketId)->first();
                if ($ticket && $ticket->dev_phone) {
                    app(WhatsAppNotificationService::class)->sendChatNotification([
                        'to' => '62' . $this->formatPhone($ticket->dev_phone),
                        'sender' => $ticket->support_name ?? 'Support',
                        'message' => mb_substr($message ?? '(attachment)',0,500),
                        'ticketId' => $ticket->id,
                        'ticketTitle' => $ticket->title,
                        'chatType' => 'support_to_dev'
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
                'sender' => 'support',
                'senderId' => $userId
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store message: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Resolve/Close a ticket
     */
    public function resolveTicket(Request $request, $id)
    {
        $ticket = DevTicket::findOrFail($id);
        if ($ticket->supportID !== Auth::id()) return back();
        $ticket->status = "Closed";
        $ticket->save();

        $developerData = User::where('id', $ticket->devID)->first();
        $developerNumber = $developerData->phone_number;
        send_notif_wa($developerNumber, 'Tiket *' . $ticket->title . '* telah ditutup oleh support: *' . Auth::user()->name . '*');

        return back();
    }

    /**
     * Reopen a resolved ticket
     * 
     * @param int $id Ticket ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reopenTicket($id)
    {
        try {
            // Cari ticket berdasarkan ID
            $ticket = DevTicket::findOrFail($id);

            // Validasi kepemilikan ticket (hanya support yang bertanggung jawab bisa reopen)
            if ($ticket->supportID !== Auth::id()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membuka tiket ini.');
            }

            // Cek apakah ticket berstatus 'Resolved' (hanya bisa reopen jika resolved)
            if ($ticket->status !== 'Resolved') {
                return redirect()->back()->with('error', 'Tiket hanya bisa dibuka kembali jika statusnya Resolved.');
            }

            // Ubah status menjadi 'In Progress'
            $ticket->status = 'In Progress';
            $ticket->save();

            // Tambahkan pesan sistem ke chat
            DB::table('devChats')->insert([
                'ticket_id' => $ticket->id,
                'sender' => 'system',
                'response' => 'Tiket dibuka kembali oleh ' . Auth::user()->name . ' (' . Auth::user()->email . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $developerData = User::where('id', $ticket->devID)->first();
            $developerNumber = $developerData->phone_number;
            send_notif_wa($developerNumber, 'Tiket *' . $ticket->title . '* telah dibuka kembali oleh support: *' . Auth::user()->name . '*');

            return redirect()->back()->with('success', 'Tiket berhasil dibuka kembali.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function formatPhone(?string $raw): ?string
    {
        if (!$raw) return null;
        $clean = preg_replace('/[^0-9]/', '', $raw);
        $clean = ltrim($clean, '0');
        return $clean;
    }
}
