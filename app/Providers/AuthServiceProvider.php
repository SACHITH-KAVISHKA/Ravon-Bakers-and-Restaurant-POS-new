<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gate for user management (admin and supervisor)
        Gate::define('manage-users', function (User $user) {
            return $user->hasManagementPrivileges();
        });

        // Define gate for modify data (create, update, delete) (admin and supervisor)
        Gate::define('modify-data', function (User $user) {
            return $user->hasManagementPrivileges();
        });

        // Define gate for supervisor access
        Gate::define('supervisor-access', function (User $user) {
            return $user->isSupervisor();
        });
    }
}