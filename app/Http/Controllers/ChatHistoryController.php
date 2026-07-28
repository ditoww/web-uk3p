<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Chat history (read-only) view for developer side with per-ticket note feature.
 * NOTE STORAGE CHANGE: The note is now stored directly in `devTickets.note` column
 * instead of a separate devTicketNotes table.
 * UI only allows adding a note once (no edit) to match existing behavior.
 */
class ChatHistoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $hasNoteColumn = Schema::hasColumn('devTickets', 'note');

        // Fetch tickets assigned to developer (same logic as ChatDevController) + include note
        $tickets = DB::table('devTickets')
            ->join('users AS support', 'devTickets.supportID', '=', 'support.id')
            ->join('users AS developer', 'devTickets.devID', '=', 'developer.id')
            ->where('devTickets.devID', '=', $userId)
            ->where('devTickets.status', '=', 'Closed')
            ->where('devTickets.isDeleted', '=', 0)
            ->select(
                'devTickets.id',
                'devTickets.title',
                'devTickets.status',
                'devTickets.created_at',
                $hasNoteColumn ? 'devTickets.note' : DB::raw('NULL AS note'),
                'support.name AS support_name',
                'support.profile_picture AS support_avatar',
                'support.email AS support_email',
                'developer.email AS developer_email',
                'developer.name AS developer_name',
                'developer.profile_picture AS developer_avatar',
                'devTickets.priority',
                DB::raw('COALESCE((SELECT MAX(created_at) FROM devChats WHERE devChats.ticket_id = devTickets.id), devTickets.created_at) as latest_chat_time')
            )
            ->orderBy('latest_chat_time', 'DESC')
            ->get();

        if ($tickets->isEmpty()) {
            return redirect()->route('Developer.developer')->with('error', 'No tickets found.');
        }

        $ticketId = $request->get('ticket', $tickets[0]->id);

        // Fetch messages for selected ticket
        $messages = DB::table('devChats')
            ->join('devTickets', 'devChats.ticket_id', '=', 'devTickets.id')
            ->leftJoin('devAttachments', 'devChats.id', '=', 'devAttachments.chatID')
            ->where('devTickets.id', '=', $ticketId)
            ->select(
                'devChats.sender',
                'devChats.response AS chat_message',
                'devChats.created_at AS chat_time',
                'devChats.check',
                'devChats.id',
                'devAttachments.filePath AS chat_image',
                'devAttachments.fileExtension AS attachment_extension'
            )
            ->orderBy('devChats.created_at', 'asc')
            ->get();

        $attachments = DB::table('devAttachments')
            ->whereIn('chatID', $messages->pluck('id'))
            ->get()
            ->groupBy('chatID');

        $selectedTicket = $tickets->firstWhere('id', $ticketId);
        if (!$selectedTicket) abort(404);
        $selectedTicket->messages = collect();

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

            if ($isImage) {
                $attList[] = [
                    'type' => 'image',
                    'url' => $normalizedUrl,
                    'name' => $displayName,
                    'size' => $att->fileSize
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

    // Use note directly from selected ticket (object with property note)
    $note = $selectedTicket->note ? (object)['note' => $selectedTicket->note] : null;

    return view('Chatdev.history', compact('selectedTicket', 'tickets', 'user', 'note'));
    }

    /**
     * Store a new note directly into devTickets.note (one-time add, no edit in current UI).
     */
    public function storeNote(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|integer',
            'note' => 'required|string',
        ]);

        $ticketId = $request->input('ticket_id');
        $existing = DB::table('devTickets')->where('id', $ticketId)->value('note');
        if ($existing === null || $existing === '') {
            DB::table('devTickets')->where('id', $ticketId)->update([
                'note' => $request->input('note'),
                'updated_at' => now(),
            ]);
            return redirect()->route('ChatHistory.index', ['ticket' => $ticketId])->with('success', 'Note saved.');
        }
        return redirect()->route('ChatHistory.index', ['ticket' => $ticketId])->with('info', 'Note already exists.');
    }

    /**
     * Delete (clear) the note from devTickets.note for a given ticket.
     */
    public function deleteNote(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|integer'
        ]);
        $ticketId = $request->input('ticket_id');
        DB::table('devTickets')->where('id', $ticketId)->update([
            'note' => null,
            'updated_at' => now(),
        ]);
        return redirect()->route('ChatHistory.index', ['ticket' => $ticketId])->with('success', 'Note deleted.');
    }
}
