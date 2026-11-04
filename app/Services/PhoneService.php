<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PhoneService
{
    protected $accessToken;
    protected $phoneId;
    protected $authTemplate;
    protected $notifTemplate;
    protected $apiVersion = 'v22.0';

    //Follows this video. It is sadly in arabic :( : https://www.youtube.com/watch?v=8yCcGDtVc64

    public function __construct()
    {
        $this->accessToken   = config('services.whatsapp.access_token');
        $this->phoneId       = config('services.whatsapp.phone_id'); //Whatsapp will give TWO phone IDs, a normal one, and a business account one. DO NOT USE THE BUSINESS ACCOUNT.
        $this->authTemplate  = config('services.whatsapp.auth_template');
        $this->notifTemplate = config('services.whatsapp.notif_template');
    }

    public function sendOtpMessage($recipientPhone, $otp) //Phone number follows THIS format: 62xxxxxxxxx88. Meaning, remove the + from country code, and don't start with 0.
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneId}/messages";

        $payload = [
            "messaging_product" => "whatsapp",
            "to"                => $recipientPhone,
            "type"              => "template",
            "template"          => [
                "name"     => $this->authTemplate,
                "language" => [
                    "code" => "en_US"
                ],
                "components" => [
                    [
                        "type"       => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $otp
                            ]
                        ]
                    ],
                    [
                        "type"       => "button",
                        "sub_type"   => "url",
                        "index"      => 0,
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $otp
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);
        return $response;
    }

    public function sendPhoneNotification($ticketId, $ticketName, $message, $status, $recipientPhone, $chatType) //It's like this because it needs DevTicket AND ClientTicket.
    {
        //chatType
        //0 = Support To Client
        //1 = Support To Developer
        //2 = Developer to Support

        $endpoints = ['support/clientchat', 'Support/chat', 'dev/chatdev'];

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneId}/messages";

        $headerText = $status === 'Resolved' || $status === 'SELESAI' ? 'Tiket bernama ' . $ticketName . 'sudah selesai!' : 'Anda mempunyai pada chat baru di tiket bernama ' . $ticketName;

        $redirectUrl = env('APP_URL') . '/' . $endpoints[$chatType] . '?ticket=' . $ticketId;

        //TODO: Make the request. Cannot proceed until work with pak anugrah since I don't have any empty numbers.
    }
}