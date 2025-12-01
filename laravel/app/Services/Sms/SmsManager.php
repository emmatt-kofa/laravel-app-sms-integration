<?php

namespace App\Services\Sms;

class SmsManager
{
    public function send(string $phone, string $message): bool
    {
        $provider = $this->resolveProvider($phone);

        return $provider->send($phone, $message);
    }

    private function resolveProvider(string $phone): SmsProvider
    {
        if (str_starts_with($phone, '+233')) {
            return new AfricaIsTalking;
            // return new Hubtel;
        }

        if (str_starts_with($phone, '+254')) {
            return new AfricaIsTalking;
        }

        return new AfricaIsTalking;
    }
}
