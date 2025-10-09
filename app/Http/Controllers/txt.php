<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Txt extends Controller
{
    public function index(Request $request)
    {
        $tickets = [];

        // Loop untuk generate 10 tiket dummy
        for ($i = 1; $i <= 10; $i++) {
            $tickets[] = [
                'id' => '#TK-2025-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'title' => "Sample Ticket Issue #$i",
                'status' => 'In Progress',
                'created_at' => rand(1, 12) . ' hours ago',
                'user' => [
                    'name' => 'User ' . $i,
                    'avatar' => 'https://i.pravatar.cc/32?img=' . rand(1, 30),
                ],
                'developer' => [
                    'name' => 'Dev ' . $i,
                    'avatar' => 'https://i.pravatar.cc/32?img=' . rand(31, 60),
                ],
                'messages' => [
                    [
                        'sender' => 'user',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(1, 30),
                        'message' => "Halo, saya mengalami masalah saat login.",
                        'time' => 'Today, 9:00 AM'
                    ],
                    [
                        'sender' => 'dev',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(31, 60),
                        'message' => "Apakah muncul pesan error tertentu?",
                        'time' => 'Today, 9:01 AM'
                    ],
                    [
                        'sender' => 'user',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(1, 30),
                        'message' => "Ya, ini tampilannya.",
                        'time' => 'Today, 9:02 AM',
                        'image' => asset('https://www.blueboxes.co.uk/_astro/hero.B--pyaWx_Z2f4qjj.jpg') // kamu harus punya file ini
                    ],
                    [
                        'sender' => 'dev',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(31, 60),
                        'message' => "Oke, saya lihat. Terima kasih atas screenshot-nya.",
                        'time' => 'Today, 9:03 AM'
                    ],
                    [
                        'sender' => 'dev',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(31, 60),
                        'message' => "Bentar saya cek dulu.",
                        'time' => 'Today, 9:05 AM'
                    ],
                    [
                        'sender' => 'user',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(1, 30),
                        'message' => "Oke.",
                        'time' => 'Today, 9:06 AM'
                    ],
                    [
                        'sender' => 'user',
                        'avatar' => 'https://i.pravatar.cc/32?img=' . rand(1, 30),
                        'message' => "Belum selesai yaa?",
                        'time' => 'Today, 11.00 PM'
                    ]
                ]
            ];
        }

        // Ambil tiket yang dipilih
        $selectedTicketId = $request->get('ticket') ?? $tickets[0]['id'];
        $selectedTicket = collect($tickets)->firstWhere('id', $selectedTicketId);

        return view('Chatdev.chatdev', compact('tickets', 'selectedTicket'));
    }
}
