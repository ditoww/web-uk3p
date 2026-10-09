<?php

if (!function_exists('success_response')) {
    function success_response($data)
    {
        return response()->json([
            'error' => false,
            'message' => 'Berhasil',
            'data' => $data
        ], 200);
    }
}

if (!function_exists('failed_response')) {
    function failed_response($data)
    {
        return response()->json([
            'error' => true,
            'message' => 'Gagal',
            'data' => $data
        ], 400);
    }
}

if (!function_exists('send_notif_wa')) {
    function send_notif_wa($nomor, $message)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post(config('services.n8n.notif_ticketing_url'), [
                    'phone_number' => $nomor,
                    'text' => $message,
                ]);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::warning('send_notif_wa gagal', [
                    'phone_number' => $nomor,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('send_notif_wa error: ' . $e->getMessage(), [
                'phone_number' => $nomor,
            ]);

            return false;
        }
    }
}
