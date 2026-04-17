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
        // Dashboard
        Permission::ViewDashboard->value => ['gate' => GlobalGate::class, 'method' => 'viewDashboard'],

        // Users
        Permission::ViewUsers->value => ['gate' => GlobalGate::class, 'method' => 'viewUsers'],
        Permission::CreateUsers->value => ['gate' => GlobalGate::class, 'method' => 'manageUsers'],
        Permission::UpdateUsers->value => ['gate' => GlobalGate::class, 'method' => 'manageUsers'],
        Permission::DeleteUsers->value => ['gate' => GlobalGate::class, 'method' => 'deleteUsers'],

        // Roles
        Permission::ViewRoles->value => ['gate' => GlobalGate::class, 'method' => 'viewRoles'],
    ];

    protected $excludedGates = [
        Permission::DeleteUsers->value,
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
            if ($user->isSuperAdmin() && !in_array($ability, $this->excludedGates)) {
                return true;
            }
        });

        foreach ($this->gates as $ability => $gate) {
            Gate::define($ability, [$gate['gate'], $gate['method']]);
        };

        $this->registerPolicies();
    }

    private function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
