<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use App\Services\AuthService;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        try {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                // Return success even if user not found to prevent email enumeration
                return response()->json(['message' => 'Jika e-mel anda berdaftar, kata laluan sementara akan dihantar.']);
            }

            // Generate a random temporary password
            $temporaryPassword = Str::upper(Str::random(4)).'-'.rand(1000, 9999);

            $user->forceFill([
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true,
            ])->setRememberToken(Str::random(60));
            $user->save();

            Mail::to($user->email)->send(new TemporaryPasswordMail($user, $temporaryPassword));

            return response()->json(['message' => 'Jika e-mel anda berdaftar, kata laluan sementara akan dihantar.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Forgot password failed: {$e->getMessage()}");

            return response()->json([
                'message' => 'Gagal menghantar e-mel. Sila hubungi pentadbir.',
            ], 500);
        }
    }

    public function captchaKey(): JsonResponse
    {
        return response()->json([
            'site_key' => $this->recaptchaService->getSiteKey(),
        ]);
    }
}
