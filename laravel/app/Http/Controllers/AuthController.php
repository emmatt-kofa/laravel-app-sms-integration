<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Jobs\SendSmsJob;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        $code = rand(100000, 999999);

        OtpCode::create([
            'phone' => $user->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        Log::info('Register for phone: '.$user->phone.' code: '.$code);

        dispatch(new SendSmsJob($user->phone, "Your verification code is: $code"));

        return response()->json([
            'message' => 'Verification code sent.',
        ]);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid phone or password',
            ], 401);
        }

        if (! $user->is_verified) {
            return response()->json([
                'message' => 'Account not verified. Please verify your phone first.',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    public function verify(VerifyOtpRequest $request)
    {
        $otp = OtpCode::where('phone', $request->phone)
            ->where('code', $request->code)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otp) {
            Log::info('Verify failed for phone: '.$request->phone.' code: '.$request->code);

            return response()->json([
                'message' => 'Invalid or expired verification code',
            ], 400);
        }

        $user = User::where('phone', $request->phone)->first();
        $user->is_verified = true;
        $user->save();

        $otp->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Phone verified successfully',
            'token' => $token,
        ]);
    }

    public function resend(ResendOtpRequest $request)
    {
        OtpCode::where('phone', $request->phone)->delete();

        $code = rand(100000, 999999);

        OtpCode::create([
            'phone' => $request->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        Log::info('Resend OTP for phone: '.$request->phone.' code: '.$code);

        dispatch(new SendSmsJob($request->phone, "Your verification code is: $code"));

        return response()->json([
            'message' => 'OTP resent successfully.',
        ]);
    }
}
