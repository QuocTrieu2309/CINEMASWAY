<?php

namespace App\Providers;

use App\Models\Movie;
use App\Models\Permission;
use App\Policies\RolePolicy;
use App\Models\Role;
use App\Models\SeatType;
use App\Policies\MoviePolicy;
use App\Models\UserPermission;
use App\Policies\PermissionPolicy;
use App\Policies\SeatTypePolicy;
use App\Policies\UserPermissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Movie::class => MoviePolicy::class,
        SeatType::class => SeatTypePolicy::class,
        UserPermission::class => UserPermissionPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}