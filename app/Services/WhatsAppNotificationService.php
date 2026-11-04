<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected string $endpoint;

    public function __construct()
    {
        $this->endpoint = rtrim(config('services.whatsapp.microservice_url', env('WHATSAPP_MICROSERVICE_URL', 'http://localhost:4001')),'/').'/send';
    }

    /**
     * Send a simple notification containing sender and message text.
     * $phone must follow configured formatting expected by the Node service (usually country code without +).
     */
    public function sendChatNotification(array $data): void
    {
        // Required keys: to, sender, message, ticketId
        try {
            $payload = [
                'to'          => $data['to'] ?? null,
                'sender'      => $data['sender'] ?? null,
                'message'     => $data['message'] ?? null,
                'ticketId'    => (int)($data['ticketId'] ?? 0),
                'ticketTitle' => $data['ticketTitle'] ?? null,
                'status'      => $data['status'] ?? null,
                'chatType'    => $data['chatType'] ?? null,
            ];

            if (!$payload['to'] || !$payload['sender'] || !$payload['message'] || !$payload['ticketId']) {
                Log::channel('whatsapp')->warning('wa.skip.missing_fields', $payload);
                return; // Silent fail
            }

            $start = microtime(true);
            $resp = Http::timeout(5)->post($this->endpoint, $payload);
            $ms = (int)((microtime(true)-$start)*1000);
            if ($resp->successful()) {
                Log::channel('whatsapp')->info('wa.sent', [
                    'ticketId' => $payload['ticketId'],
                    'to' => $payload['to'],
                    'chatType' => $payload['chatType'],
                    'status' => $payload['status'],
                    'duration_ms' => $ms
                ]);
            } else {
                Log::channel('whatsapp')->error('wa.failed', [
                    'ticketId' => $payload['ticketId'],
                    'to' => $payload['to'],
                    'code' => $resp->status(),
                    'body' => $resp->body(),
                    'duration_ms' => $ms
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('wa.exception', [
                'message' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(),0,1000)
            ]);
        }
    }
}
