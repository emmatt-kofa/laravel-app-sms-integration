<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricaIsTalking implements SmsProvider
{
    public function send(string $phone, string $message): bool
    {
        $baseUrl = config('sms.africatalking.sandbox')
            ? 'https://api.sandbox.africastalking.com'
            : 'https://api.africastalking.com';

        $response = Http::asForm()->withHeaders([
            'apiKey' => config('sms.africatalking.api_key'),
        ])->post("$baseUrl/version1/messaging", [
            'username' => config('sms.africatalking.username'),
            'to' => $phone,
            'message' => $message,
            // 'from' => config('sms.sender'),
        ]);

        // Log::info('AfricaIsTalking SMS response', $response->json());

        // if (! $response->successful()) {
        //     Log::error('AfricaIsTalking SMS failed', [
        //         'status' => $response->status(),
        //         'body' => $response->body(),
        //     ]);
        // }

        // return $response->successful();

        $responseBody = $response->body();
        Log::info('AfricaIsTalking SMS response (raw)', [
            'phone' => $phone,
            'message' => $message,
            'response_body' => $responseBody,
        ]);

        // Log decoded JSON if available
        $decoded = json_decode($responseBody, true);
        if ($decoded) {
            Log::info('AfricaIsTalking SMS response (decoded)', $decoded);
        }

        if (! $response->successful()) {
            Log::error('AfricaIsTalking SMS failed', [
                'status' => $response->status(),
                'body' => $responseBody,
            ]);
        }

        return $response->successful();
    }
}
