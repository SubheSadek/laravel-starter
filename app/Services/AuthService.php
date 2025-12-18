<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyUserRequest;
use App\Mail\UserOtpMail;
use App\Models\User;
use App\Models\UserOtp;
use App\Traits\FormatterTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    use FormatterTrait;

    /**
     * Format validated data
     */
    public function formatValidatedData(array $validatedData): array
    {
        if ($address = $validatedData['address'] ?? null) {
            $validatedData['address'] = $this->plainTextPurifier($address);
        }

        $validatedData['status'] = UserStatus::PENDING;

        return $validatedData;
    }

    /**
     * Create a new user instance after a valid registration.
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Send OTP
     */
    public function sendOtp(User $user): void
    {
        $userOtp = $this->generateAndSaveOtp($user->id);
        $this->sendOtpEmail($user, $userOtp);
    }

    /**
     * Generate and save OTP
     */
    private function generateAndSaveOtp(int $userId): UserOtp
    {
        $otp = rand(100000, 999999);

        $userOtp = new UserOtp;
        $userOtp->user_id = $userId;
        $userOtp->otp = (string) $otp;
        $userOtp->expires_at = now()->addMinutes(5);
        $userOtp->save();

        return $userOtp;
    }

    /**
     * Send OTP email
     */
    private function sendOtpEmail(User $user, UserOtp $userOtp): void
    {
        Mail::to($user->email)
            ->queue(new UserOtpMail($userOtp->otp, $user));
    }

    /**
     * Find validated user
     */
    public function findValidatedUser(VerifyUserRequest $request): User
    {
        $user = User::where('email', $request->email)->first();

        if (
            empty($user)
            || $user->status !== UserStatus::PENDING->value
            || ! Hash::check($request->password, $user->password)
        ) {
            abort(404, 'Invalid user credentials!');
        }

        return $user;
    }

    /**
     * Find validated OTP user
     */
    public function findValidatedOtpUser(VerifyUserRequest $request, User $user): UserOtp
    {
        $userOtp = UserOtp::where('user_id', $user->id)->latest('id')->first();

        if (empty($userOtp) || $userOtp->otp !== $request->otp) {
            abort(404, 'Invalid OTP!');
        }

        if ($userOtp->expires_at < now()) {
            abort(404, 'OTP expired!');
        }

        return $userOtp;
    }

    /**
     * Find and validate user
     */
    public function findAndValidateUser(LoginRequest $request): User
    {
        $user = User::where('email', $request->email)->first();

        if (
            empty($user)
            || $user->status !== UserStatus::ACTIVE->value
            || ! Hash::check($request->password, $user->password)
        ) {
            abort(404, 'Invalid user credentials!');
        }

        return $user;
    }
}
