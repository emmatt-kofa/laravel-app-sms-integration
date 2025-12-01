<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;

class Hubtel implements SmsProvider
{
    public function send(string $phone, string $message): bool
    {
        $response = Http::withBasicAuth(
            config('sms.hubtel.client_id'),
            config('sms.hubtel.client_secret')
        )->post('https://smsc.hubtel.com/v1/messages/send', [
            'from' => config('sms.sender'),
            'to' => $phone,
            'content' => $message,
        ]);

        return $response->successful();
    }
}
