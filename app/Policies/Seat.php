<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class MoviePolicy
{
    public function checkPermission(User $user)
    {
        return CheckPermissionWithPolicy($user,'Seat')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
