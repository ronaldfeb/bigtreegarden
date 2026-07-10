<?php

namespace App\Providers;

use App\Enums\StaffRole;
use App\Models\StaffUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureFilesystemDisks();
        $this->configureDefaults();
        $this->configureStaffGates();
    }

    protected function configureFilesystemDisks(): void
    {
        $cloudPublicDisk = config('filesystems.disks.btg_public');

        if (! is_array($cloudPublicDisk) || ($cloudPublicDisk['driver'] ?? null) !== 's3') {
            return;
        }

        Config::set('filesystems.disks.public', $cloudPublicDisk);
    }

    protected function configureStaffGates(): void
    {
        $activeStaff = fn (?StaffUser $staffUser): bool => $staffUser !== null && $staffUser->is_active;

        Gate::define('staff-admin', fn (User $user): bool => $activeStaff($user->staffUser)
            && $user->staffUser->role === StaffRole::Admin);

        Gate::define('staff-marketing', fn (User $user): bool => $activeStaff($user->staffUser)
            && in_array($user->staffUser->role, [StaffRole::Admin, StaffRole::Marketing], true));

        Gate::define('staff-content', fn (User $user): bool => $activeStaff($user->staffUser)
            && in_array($user->staffUser->role, [StaffRole::Admin, StaffRole::Content], true));

        Gate::define('staff-support', fn (User $user): bool => $activeStaff($user->staffUser)
            && in_array($user->staffUser->role, [StaffRole::Admin, StaffRole::Support], true));

        Gate::define('manage-marketing', fn (User $user): bool => $activeStaff($user->staffUser)
            && in_array($user->staffUser->role, [StaffRole::Admin, StaffRole::Marketing], true));

        Gate::define('manage-content', fn (User $user): bool => $activeStaff($user->staffUser)
            && in_array($user->staffUser->role, [StaffRole::Admin, StaffRole::Content], true));

        Gate::define('manage-directory', fn (User $user): bool => $activeStaff($user->staffUser)
            && in_array($user->staffUser->role, [StaffRole::Admin, StaffRole::Support], true));

        Gate::define('manage-commerce', fn (User $user): bool => $activeStaff($user->staffUser)
            && $user->staffUser->role === StaffRole::Admin);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
