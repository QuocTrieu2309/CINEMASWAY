<?php

namespace App\Providers;

use App\Models\Cinema;
use App\Models\CinemaScreen;
use App\Models\Movie;
use App\Models\Permission;
use App\Policies\RolePolicy;
use App\Models\Role;
use App\Models\Screen;
use App\Models\Seat;
use App\Models\SeatMap;
use App\Models\SeatType;
use App\Models\Service;
use App\Models\Showtime;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Policies\MoviePolicy;
use App\Models\UserPermission;
use App\Policies\CinemaPolicy;
use App\Policies\CinemaScreenPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ScreenPolicy;
use App\Policies\SeatMapPolicy;
use App\Policies\SeatPolicy;
use App\Policies\SeatTypePolicy;
use App\Policies\ServicePolicy;
use App\Policies\ShowtimePolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPermissionPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Movie::class => MoviePolicy::class,
        SeatType::class => SeatTypePolicy::class,
        Showtime::class => ShowtimePolicy::class,
        UserPermission::class => UserPermissionPolicy::class,
        Screen::class => ScreenPolicy::class,
        Cinema::class => CinemaPolicy::class,
        CinemaScreen::class => CinemaScreenPolicy::class,
        Ticket::class => TicketPolicy::class,
        Seat::class => SeatPolicy::class,
        SeatMap::class => SeatMapPolicy::class,
        Service::class => ServicePolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
