<?php

namespace App\Http\Controllers;

use App\Models\DevNoteBlob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DevNotesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $notes = DevNoteBlob::where('user_id', $user->id)->orderByDesc('updated_at')->get();

        $activeNote = null;
        if ($request->filled('id')) {
            $activeNote = $notes->firstWhere('id', (int)$request->get('id'));
        } elseif ($notes->isNotEmpty()) {
            $activeNote = $notes->first();
        }

        $noteContent = '';
        if ($activeNote) {
            $path = "notes/{$user->id}/{$activeNote->id}.txt";
            if (Storage::disk('public')->exists($path)) {
                $noteContent = Storage::disk('public')->get($path);
            }
        }

        return view('Chatdev.notes', compact('notes', 'activeNote', 'noteContent', 'user'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'nullable|max:191'
        ],[
            'title.max' => 'Mohon berikan judul note yang lebih pendek.'
        ]);

        $title = trim((string)$request->input('title', 'Untitled')) ?: 'Untitled';

        $note = DevNoteBlob::create([
            'user_id' => $user->id,
            'title' => $title,
            'file_name' => '', // diisi setelah tahu id
            'mime_type' => 'text/plain',
            'size' => 0,
            'blob' => '',
        ]);

        $path = "notes/{$user->id}/{$note->id}.txt";
        Storage::disk('public')->put($path, '');
        $note->update(['file_name' => "{$note->id}.txt"]);

        return response()->json([
            'ok' => true,
            'id' => $note->id,
            'redirect' => route('DevNotes.index', ['id' => $note->id]),
        ]);
    }

    public function save(Request $request, DevNoteBlob $note)
    {
        $user = Auth::user();
        abort_if($note->user_id !== $user->id, 403);

        $content = (string)$request->input('content', '');
        $path = "notes/{$user->id}/{$note->id}.txt";

        Storage::disk('public')->put($path, $content);

        $note->update([
            'file_name' => "{$note->id}.txt",
            'mime_type' => 'text/plain',
            'size' => strlen($content),
            'blob' => $content,
        ]);

        return response()->json([
            'ok' => true,
            'saved_at' => Carbon::now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        ]);
    }

    public function rename(Request $request, DevNoteBlob $note)
    {
        $user = Auth::user();
        abort_if($note->user_id !== $user->id, 403);

        $request->validate([
            'title' => 'nullable|max:191'
        ],[
            'title.max' => 'Mohon berikan judul note yang lebih pendek.'
        ]);

        $title = trim((string)$request->input('title', 'Untitled')) ?: 'Untitled';
        $note->update(['title' => $title]);

        return response()->json(['ok' => true]);
    }

    public function destroy(DevNoteBlob $note)
    {
        $user = Auth::user();
        abort_if($note->user_id !== $user->id, 403);

        $path = "notes/{$user->id}/{$note->id}.txt";
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        $note->delete();

        return response()->json(['ok' => true]);
    }

    public function download(DevNoteBlob $note)
    {
        $user = Auth::user();
        abort_if($note->user_id !== $user->id, 403);

        $path = "notes/{$user->id}/{$note->id}.txt";
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $note->blob ?? '');
        }

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('public')->get($path);
        }, "{$note->title}.txt", ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}