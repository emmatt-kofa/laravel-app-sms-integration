<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        Bus::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Emma TT',
            'email' => 'emma@gmail.com',
            'phone' => '233558664534',
            'password' => 'Web@2020',
            'password_confirmation' => 'Web@2020',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'emma@gmail.com',
            'phone' => '233558664534',
        ]);

        $this->assertDatabaseHas('otp_codes', [
            'phone' => '233558664534',
        ]);

        Bus::assertDispatched(SendSmsJob::class);
    }

    public function test_user_cannot_login_without_verification()
    {
        $user = User::factory()->create([
            'phone' => '233558664534',
            'password' => bcrypt('Web@2020'),
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'phone' => '233558664534',
            'password' => 'Web@2020',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_verify_otp()
    {
        $user = User::factory()->create([
            'phone' => '233558664534',
            'is_verified' => false,
        ]);

        $otp = OtpCode::create([
            'phone' => '233558664534',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '233558664534',
            'code' => '123456',
        ]);

        $response->assertStatus(200);

        // $this->assertDatabaseMissing('otp_codes', ['id' => $otp->id]);
        // $this->assertTrue($user->fresh()->is_verified);
    }

    public function test_verify_otp_fails_with_invalid_code()
    {
        $user = User::factory()->create([
            'phone' => '233558664534',
            'is_verified' => false,
        ]);

        OtpCode::create([
            'phone' => '233558664534',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '233558664534',
            'code' => '654321',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_can_login_after_verification()
    {
        $user = User::factory()->create([
            'phone' => '233558664534',
            'password' => bcrypt('Web@2020'),
            'is_verified' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'phone' => '233558664534',
            'password' => 'Web@2020',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_resend_otp()
    {
        Bus::fake();

        $user = User::factory()->create([
            'phone' => '233558664534',
        ]);

        $response = $this->postJson('/api/resend-otp', [
            'phone' => '233558664534',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('otp_codes', [
            'phone' => '233558664534',
        ]);

        Bus::assertDispatched(SendSmsJob::class);
    }
}
