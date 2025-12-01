<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class OtpRateLimitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(1)->by($request->input('phone') ?? $request->ip());
        });

        RateLimiter::for('otp_resend', function (Request $request) {
            return Limit::perMinute(1)->by($request->input('phone') ?? $request->ip());
        });

        RateLimiter::for('otp_verify', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->input('phone') ?? $request->ip()),
                Limit::perMinutes(15, 10)->by($request->input('phone') ?? $request->ip()),
            ];
        });
    }
}
