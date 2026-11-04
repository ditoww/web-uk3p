<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\ClientChat;
use App\Models\DevChat;

class FileController extends Controller
{
    public function download($filename)
    {
        $parts = explode("_", $filename, 2);
        $chatId = $parts[0] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = ClientChat::findOrFail($chatId);
        $ticket = $chat->clientTicket;

        if (!in_array(Auth::id(), [$ticket->userID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }

        $path = "client_attachments/{$filename}";

        $downloadName = preg_replace('/^\d+_/', '', $filename);

        return Storage::disk('public')->download($path, $downloadName);
    }

    public function downloadSupport($filename)
    {
        $parts = explode("_", $filename);
        $chatId = $parts[1] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = ClientChat::findOrFail($chatId);
        $ticket = $chat->clientTicket;

        if (!in_array(Auth::id(), [$ticket->userID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }

        $path = "chat_attachments/{$filename}";

        $downloadName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_[1-4]_/', '', $filename);

        return Storage::disk('support')->download($path, $downloadName);
    }

    public function downloadSupportChat($filename)
    {
        $parts = explode("-", explode("_", $filename)[0]);
        $chatId = $parts[2] ?? null;
        
        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = DevChat::findOrFail($chatId);
        $ticket = $chat->devTicket;

        if (!in_array(Auth::id(), [$ticket->devID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }
        
        $path = "chat_attachments/{$filename}";

        $downloadName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_/', '', $filename);

        return Storage::disk('support')->download($path, $downloadName);
    }

    public function downloadDev($filename)
    {
        $parts = explode("_", $filename);
        $chatId = $parts[1] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = DevChat::findOrFail($chatId);
        $ticket = $chat->devTicket;

        if (!in_array(Auth::id(), [$ticket->devID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }
        
        $path = "chat_attachments/{$filename}";

        $downloadName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_dev_\d+_/', '', $filename);

        return Storage::disk('support')->download($path, $downloadName);
    }

    public function view($filename)
    {
        $parts = explode("_", $filename, 2);
        $chatId = $parts[0] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = ClientChat::findOrFail($chatId);
        $ticket = $chat->clientTicket;

        if (!in_array(Auth::id(), [$ticket->userID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }

        $path = "client_attachments/{$filename}";

        $downloadName = preg_replace('/^\d+_/', '', $filename);

        return response()->stream(function () use ($path) {
            echo Storage::disk('public')->get($path);
        }, 200, [
            'Content-Type'        => Storage::disk('public')->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"'
        ]);
    }

    public function viewSupport($filename)
    {
        $parts = explode("_", $filename);
        $chatId = $parts[1] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = ClientChat::findOrFail($chatId);
        $ticket = $chat->clientTicket;

        if (!in_array(Auth::id(), [$ticket->userID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }
        
        $path = "chat_images/{$filename}";

        $downloadName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_[1-4]_/', '', $filename);

        return response()->stream(function () use ($path) {
            echo Storage::disk('support')->get($path);
        }, 200, [
            'Content-Type'        => Storage::disk('support')->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"'
        ]);
    }

    public function viewSupportChat($filename)
    {
        $parts = explode("-", explode("_", $filename)[0]);
        $chatId = $parts[2] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = DevChat::findOrFail($chatId);
        $ticket = $chat->devTicket;

        if (!in_array(Auth::id(), [$ticket->devID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }
        
        $path = "chat_images/{$filename}";

        $downloadName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_/', '', $filename);

        return response()->stream(function () use ($path) {
            echo Storage::disk('support')->get($path);
        }, 200, [
            'Content-Type'        => Storage::disk('support')->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"'
        ]);
    }

    public function viewDev($filename)
    {
        $parts = explode("_", $filename);
        $chatId = $parts[1] ?? null;

        if (!$chatId) {
            return back()->with('error', 'Filename Invalid.');
        }

        $chat = DevChat::findOrFail($chatId);
        $ticket = $chat->devTicket;

        if (!in_array(Auth::id(), [$ticket->devID, $ticket->supportID])) {
            return back()->with('error', 'Unauthorized access.');
        }
        
        $path = "chat_images/{$filename}";

        $downloadName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_dev_\d+_/', '', $filename);

        return response()->stream(function () use ($path) {
            echo Storage::disk('support')->get($path);
        }, 200, [
            'Content-Type'        => Storage::disk('support')->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"'
        ]);
    }

    public function pfp($filename)
    {
        $path = "users/{$filename}";

        $downloadName = preg_replace('/^\d+_/', '', $filename);

        return response()->stream(function () use ($path) {
            echo Storage::disk('public')->get($path);
        }, 200, [
            'Content-Type'        => Storage::disk('public')->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"'
        ]);
    }
}
