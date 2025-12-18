<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\UserStatus;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyUserRequest;
use App\Mail\UserOtpMail;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService;
    }

    /**
     * Summary of test_formats_validated_data_with_address
     */
    public function test_formats_validated_data_with_address(): void
    {
        $validatedData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'address' => '  <script>alert("xss")</script>123 Main St  ',
        ];

        $result = $this->authService->formatValidatedData($validatedData);

        $this->assertEquals('123 Main St', $result['address']);
        $this->assertEquals(UserStatus::PENDING, $result['status']);
        $this->assertStringNotContainsString('<script>', $result['address']);
    }

    /**
     * Summary of test_creates_user_with_given_data
     */
    public function test_creates_user_with_given_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'status' => UserStatus::PENDING,
            'address' => '456 Elm St',
        ];

        $user = $this->authService->createUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('456 Elm St', $user->address);
        $this->assertEquals(UserStatus::PENDING, $user->status);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'address' => '456 Elm St',
        ]);
    }

    /**
     * Summary of test_sends_otp_to_user
     */
    public function test_sends_otp_to_user(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => UserStatus::PENDING,
        ]);

        $this->authService->sendOtp($user);

        Mail::assertQueued(UserOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Summary of test_generates_six_digit_otp
     */
    public function test_generates_six_digit_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'status' => UserStatus::PENDING,
        ]);

        $this->authService->sendOtp($user);

        $userOtp = UserOtp::where('user_id', $user->id)->latest('id')->first();

        $this->assertNotNull($userOtp);
        $this->assertEquals(6, strlen($userOtp->otp));
        $this->assertIsNumeric($userOtp->otp);
        $this->assertGreaterThanOrEqual(100000, (int) $userOtp->otp);
        $this->assertLessThanOrEqual(999999, (int) $userOtp->otp);
    }

    /**
     * Summary of test_queues_otp_email
     */
    public function test_queues_otp_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => UserStatus::PENDING,
        ]);

        $this->authService->sendOtp($user);

        Mail::assertQueued(UserOtpMail::class, 1);
        Mail::assertQueued(UserOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /**
     * Test findValidatedUser with valid credentials
     */
    public function test_find_validated_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'status' => UserStatus::PENDING->value,
        ]);

        $request = $this->createVerifyUserRequest([
            'email' => 'test@example.com',
            'password' => 'password123',
            'otp' => '123456',
        ]);

        $result = $this->authService->findValidatedUser($request);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->email, $result->email);
    }

    /**
     * Test findValidatedUser with non-existent email
     */
    public function test_find_validated_user_with_non_existent_email(): void
    {
        $request = $this->createVerifyUserRequest([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
            'otp' => '123456',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid user credentials!');

        $this->authService->findValidatedUser($request);
    }

    /**
     * Test findValidatedOtpUser with valid OTP
     */
    public function test_find_validated_otp_user_with_otp(): void
    {
        $user = User::factory()->create();

        $userOtp = UserOtp::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(5),
        ]);

        $request = $this->createVerifyUserRequest([
            'email' => $user->email,
            'password' => 'password123',
            'otp' => '123456',
        ]);

        $result = $this->authService->findValidatedOtpUser($request, $user);

        $this->assertInstanceOf(UserOtp::class, $result);
        $this->assertEquals($userOtp->id, $result->id);
        $this->assertEquals($userOtp->otp, $result->otp);
    }

    /**
     * Test findValidatedOtpUser with expired OTP
     */
    public function test_find_validated_otp_user_with_expired_otp(): void
    {
        $user = User::factory()->create();

        UserOtp::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'expires_at' => now()->subMinutes(1),
        ]);

        $request = $this->createVerifyUserRequest([
            'email' => $user->email,
            'password' => 'password123',
            'otp' => '123456',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('OTP expired!');

        $this->authService->findValidatedOtpUser($request, $user);
    }

    /**
     * Test findAndValidateUser with valid credentials and active status
     */
    public function test_find_and_validate_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'status' => UserStatus::ACTIVE->value,
        ]);

        $request = $this->createLoginRequest([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $result = $this->authService->findAndValidateUser($request);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->email, $result->email);
        $this->assertEquals(UserStatus::ACTIVE->value, $result->status);
    }

    /**
     * Test findAndValidateUser with non-existent email
     */
    public function test_find_and_validate_user_with_non_existent_email(): void
    {
        $request = $this->createLoginRequest([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid user credentials!');

        $this->authService->findAndValidateUser($request);
    }

    /**
     * Test findAndValidateUser with multiple users (ensures correct user is returned)
     */
    public function test_find_and_validate_user_with_multiple_users(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'user1@example.com',
            'password' => 'password123',
            'status' => UserStatus::ACTIVE->value,
        ]);

        $targetUser = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => 'password456',
            'status' => UserStatus::ACTIVE->value,
        ]);

        User::factory()->create([
            'email' => 'user3@example.com',
            'password' => 'password789',
            'status' => UserStatus::ACTIVE->value,
        ]);

        $request = $this->createLoginRequest([
            'email' => 'user2@example.com',
            'password' => 'password456',
        ]);

        $result = $this->authService->findAndValidateUser($request);

        $this->assertEquals($targetUser->id, $result->id);
        $this->assertEquals('user2@example.com', $result->email);
    }

    /**
     * Test findAndValidateUser with wrong password
     */
    public function test_find_and_validate_user_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'correct_password',
            'status' => UserStatus::ACTIVE->value,
        ]);

        $request = $this->createLoginRequest([
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid user credentials!');

        $this->authService->findAndValidateUser($request);
    }

    /**
     * Helper method to create a mock VerifyUserRequest
     */
    private function createVerifyUserRequest(array $data): VerifyUserRequest
    {
        $request = new VerifyUserRequest;
        $request->merge($data);

        return $request;
    }
    
    /**
     * Helper method to create a mock LoginRequest
     */
    private function createLoginRequest(array $data): LoginRequest
    {
        $request = new LoginRequest;
        $request->merge($data);

        return $request;
    }
}
