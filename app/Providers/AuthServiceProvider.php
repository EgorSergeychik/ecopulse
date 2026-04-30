<?php

namespace App\Providers;

use App\Support\Enums\Permission;
use App\Support\Enums\Role;
use App\Support\RBAC\RoleRegistry;
use Domain\Robot\Models\Robot;
use Domain\Robot\Policies\RobotPolicy;
use Domain\User\Models\User;
use Domain\User\Policies\UserPolicy;
use Domain\Zone\Models\Zone;
use Domain\Zone\Policies\ZonePolicy;
use Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->defineRoles();
        $this->definePolicies();
    }

    private function defineRoles(): void
    {
        RoleRegistry::define(
            Role::OPERATOR->value,
            Permission::ViewDashboard,

            Permission::ViewZones,
            Permission::UpdateZones,

            Permission::ViewRobots,
            Permission::TransitionRobots,
        );
    }

    private function definePolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Zone::class, ZonePolicy::class);
        Gate::policy(Robot::class, RobotPolicy::class);
    }
}
