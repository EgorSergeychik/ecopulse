<?php

namespace App\Providers;

use App\Support\Enums\Permission;
use App\Support\Gates\GlobalGate;
use Domain\User\Models\User;
use Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    protected $gates = [
        Permission::ViewDashboard->value => ['gate' => GlobalGate::class, 'method' => 'viewDashboard'],
        Permission::ManageUsers->value => ['gate' => GlobalGate::class, 'method' => 'manageUsers'],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        foreach ($this->gates as $ability => $gate) {
            Gate::define($ability, [$gate['gate'], $gate['method']]);
        };
    }
}
