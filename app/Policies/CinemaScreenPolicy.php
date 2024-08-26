<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\CinemaScreens;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class CinemaScreenPolicy
{
    public function checkPermission(User $user)
    {
        return CheckPermissionWithPolicy($user,'Cinema-Screen')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
    public function delete(User $user){
        return CheckPermissionWithPolicy($user,'Cinema-Screen')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
