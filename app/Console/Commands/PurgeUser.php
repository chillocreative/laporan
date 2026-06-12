<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PurgeUser extends Command
{
    protected $signature = 'users:purge
                            {email : The email address of the user to inspect/purge}
                            {--force : Permanently delete without confirmation (also required when the user has reports)}';

    protected $description = 'Inspect and permanently delete a user (including soft-deleted) by email so the address can be reused';

    public function handle(): int
    {
        $email = trim($this->argument('email'));

        $user = User::withTrashed()->where('email', $email)->first();

        if (! $user) {
            $this->info("No user (active or deleted) found with email: {$email}");
            $this->line('The email is free to register.');

            return self::SUCCESS;
        }

        $reportsCount = $user->reports()->count();

        $this->table(
            ['ID', 'Name', 'Email', 'Active', 'Deleted at', 'Roles', 'Reports'],
            [[
                $user->id,
                $user->name,
                $user->email,
                $user->is_active ? 'yes' : 'no',
                $user->deleted_at?->toDateTimeString() ?? '— (active)',
                $user->roles()->pluck('name')->implode(', ') ?: '—',
                $reportsCount,
            ]]
        );

        if ($reportsCount > 0 && ! $this->option('force')) {
            $this->error("This user has {$reportsCount} report(s). Permanently deleting will CASCADE-delete those reports.");
            $this->line('Re-run with --force only if you are sure you want to lose them.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Permanently delete this user and free up '{$email}'?")) {
            $this->info('Aborted. No changes made.');

            return self::SUCCESS;
        }

        $user->roles()->detach();
        $user->forceDelete();

        $this->info("User permanently deleted. '{$email}' is now free to register.");

        return self::SUCCESS;
    }
}
