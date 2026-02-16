<?php

namespace App\Providers;

use App\Events\ReportCreated;
use App\Listeners\QueueReportAnalysis;
use App\Models\Report;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Policies\ReportPolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\UserPolicy;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\OpenAILogRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SecurityLogRepositoryInterface;
use App\Repositories\Contracts\SettingsRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Repositories\Eloquent\OpenAILogRepository;
use App\Repositories\Eloquent\ReportRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SecurityLogRepository;
use App\Repositories\Eloquent\SettingsRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\SettingsService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);
        $this->app->bind(SecurityLogRepositoryInterface::class, SecurityLogRepository::class);
        $this->app->bind(OpenAILogRepositoryInterface::class, OpenAILogRepository::class);
    }

    public function boot(): void
    {
        // Policy registrations
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Setting::class, SettingsPolicy::class);

        // Event listeners
        Event::listen(ReportCreated::class, QueueReportAnalysis::class);

        // Custom password reset URL for SPA
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.url').'/reset-password?token='.$token.'&email='.urlencode($user->email);
        });

        // Override mail config from database SMTP settings
        try {
            app(SettingsService::class)->applySmtpConfig();
        } catch (\Throwable $e) {
            // Silently fail if DB is not yet migrated
        }

        // Super admin bypass — super-admin gets all permissions
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}
