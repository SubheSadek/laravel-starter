<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Enums\UserStatus;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyUserRequest;
use App\Http\Resources\AuthResource;
use App\Http\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): Response
    {

        $formattedData = $this->authService->formatValidatedData($request->validated());

        try {
            DB::beginTransaction();
            $user = $this->authService->createUser($formattedData);
            $this->authService->sendOtp($user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return withError('Registration failed!');
        }

        return withSuccess(
            message: 'Registration successful! '.
            'We have sent a six digit code to your email. '.
            'Please check your email and enter the code to complete your registration.'
        );
    }

    /**
     * Verify a user.
     */
    public function verifyUser(VerifyUserRequest $request): Response
    {
        try {
            $user = $this->authService->findValidatedUser($request);
            $userOtp = $this->authService->findValidatedOtpUser($request, $user);
        } catch (\Exception $e) {
            return withError($e->getMessage());
        }

        try {
            DB::beginTransaction();
            $user->status = UserStatus::ACTIVE;
            $user->save();

            $userOtp->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return withError('Verification failed!');
        }

        return withSuccess(message: 'User verified successfully!');
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request): Response
    {
        try {
            $user = $this->authService->findAndValidateUser($request);
        } catch (\Exception $e) {
            return withError($e->getMessage());
        }

        $token = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;

        $user->access_token = $token;

        return withSuccess(new AuthResource($user), 'Login successful!');
    }

    /**
     * Get authenticated user.
     */
    public function authUser(Request $request): Response
    {
        return withSuccess(new AuthResource($request->user()));
    }

    /**
     * Logout a user.
     */
    public function logout(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return withSuccess(message: 'Logout successful!');
    }
}
