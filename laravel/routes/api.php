<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:otp');
Route::post('/verify-otp', [AuthController::class, 'verify'])->middleware('throttle:otp_verify');
Route::post('/resend-otp', [AuthController::class, 'resend'])->middleware('throttle:otp_resend');

Route::post('/login', [AuthController::class, 'login']);
