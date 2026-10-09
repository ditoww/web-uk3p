<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'), // NOT BUSINESS PHONE ID
        'auth_template' => env('WHATSAPP_AUTH_TEMPLATE_NAME'),
        'notif_template' => env('WHATSAPP_NOTIFICATION_TEMPLATE_NAME'),
        'microservice_url' => env('WHATSAPP_MICROSERVICE_URL', 'http://localhost:4001')
    ],

    'n8n' => [
        'notif_ticketing_url' => env('N8N_NOTIF_TICKETING_URL'),
    ],

];
