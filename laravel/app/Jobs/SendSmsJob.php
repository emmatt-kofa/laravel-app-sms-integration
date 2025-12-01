<?php

namespace App\Jobs;

use App\Services\Sms\SmsManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $phone;

    private string $message;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(): void
    {
        $sent = app(SmsManager::class)->send($this->phone, $this->message);

        if ($sent) {
            Log::info('SMS sent successfully', ['phone' => $this->phone]);
        } else {
            Log::error('SMS failed to send', ['phone' => $this->phone]);
        }
    }
}
