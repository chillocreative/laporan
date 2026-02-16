<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected RecaptchaService $recaptchaService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        if (! $this->recaptchaService->verify($request->input('recaptcha_token'))) {
            throw ValidationException::withMessages([
                'recaptcha_token' => ['Pengesahan CAPTCHA gagal.'],
            ]);
        }

        $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Pendaftaran berjaya. Akaun anda sedang menunggu kelulusan daripada pentadbir. Anda akan menerima notifikasi e-mel setelah diluluskan.',
            'pending_approval' => true,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'Log masuk berjaya.',
            'user' => new UserResource($user->load('roles.permissions')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'Berjaya log keluar.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('roles.permissions')),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Pautan tetapan semula kata laluan telah dihantar ke e-mel anda.']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Kata laluan berjaya ditetapkan semula.']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function captchaKey(): JsonResponse
    {
        return response()->json([
            'site_key' => $this->recaptchaService->getSiteKey(),
        ]);
    }
}
