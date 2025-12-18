<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration.
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => '123 Main St',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /*
     * Test user register validation fails.
     */
    #[DataProvider('invalidRegisterDataProvider')]
    public function test_register_validation_fails(array $data): void
    {
        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('messages', fn ($value) => ! empty($value));
    }

    /**
     * Test user register validation fails.
     */
    public static function invalidRegisterDataProvider(): array
    {
        return [
            'missing name' => [
                ['email' => 'test@example.com'],
                ['password' => 'password'],
                ['password_confirmation' => 'password'],
                ['address' => '123 Main St'],
            ],
            'missing email' => [
                ['name' => 'John Doe'],
                ['password' => 'password'],
                ['password_confirmation' => 'password'],
                ['address' => '123 Main St'],
            ],
            'missing password' => [
                ['name' => 'John Doe'],
                ['email' => 'test@example.com'],
                ['password_confirmation' => 'password'],
                ['address' => '123 Main St'],
            ],
            'missing password confirmation' => [
                ['name' => 'John Doe'],
                ['email' => 'test@example.com'],
                ['password' => 'password'],
                ['address' => '123 Main St'],
            ],
        ];
    }

    /**
     * Test user verification.
     */
    public function test_user_can_verify_successfully(): void
    {
        $user = User::factory()->create([
            'status' => 'pending',
            'password' => Hash::make('secret123'),
        ]);

        $otp = UserOtp::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify-user', [
            'email' => $user->email,
            'password' => 'secret123',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('user_otps', [
            'id' => $otp->id,
        ]);
    }

    /**
     * Test user login.
     */
    public function test_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('json_data.access_token', fn ($value) => ! empty($value))
            ->assertJsonPath('success', true);
    }

    /**
     * Test user get auth user.
     */
    public function test_user_get_auth_user_successfully(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->get('/api/auth/auth-user');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('json_data', fn ($value) => ! empty($value));
    }

    /**
     * Test user logout.
     */
    public function test_user_can_logout_successfully(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
