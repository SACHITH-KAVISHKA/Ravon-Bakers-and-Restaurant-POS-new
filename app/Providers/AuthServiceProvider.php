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
        // Define gate for user management
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        // Define gate for modify data (create, update, delete)
        Gate::define('modify-data', function (User $user) {
            return $user->isAdmin();
        });
    }
}