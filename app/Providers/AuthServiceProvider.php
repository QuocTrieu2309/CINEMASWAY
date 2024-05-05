<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Permission;
use App\Policies\RolePolicy;
use App\Models\Role;
use App\Models\SeatType;
use App\Models\UserPermission;
use App\Policies\PermissionPolicy;
use App\Policies\SeatTypePolicy;
use App\Policies\UserPermissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        SeatType::class => SeatTypePolicy::class,
        UserPermission::class => UserPermissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
