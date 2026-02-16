<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ActivityLogService $activityLogService,
        protected SecurityLogService $securityLogService,
    ) {}

    public function register(array $data): User
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => false,
        ]);

        $this->activityLogService->log('user_registered', $user, 'New user registered (pending approval)');

        return $user;
    }

    public function login(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->securityLogService->logFailedLogin($credentials['email']);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            $hasRoles = $user->roles()->exists();
            throw ValidationException::withMessages([
                'email' => [$hasRoles
                    ? 'Your account has been deactivated.'
                    : 'Your account is pending approval from an administrator.'],
            ]);
        }

        Auth::login($user);

        $this->activityLogService->log('user_login', $user, 'User logged in');

        return $user->load('roles');
    }

    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            $this->activityLogService->log('user_logout', $user, 'User logged out');
        }

        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
