<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\SeatType;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class SeatTypePolicy
{
    public function checkPermission(User $user)
    {
        return CheckPermissionWithPolicy($user,'Seat-Type')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
    public function delete(User $user){
        return CheckPermissionWithPolicy($user,'Seat-Type')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
