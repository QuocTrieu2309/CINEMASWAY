<?php

namespace App\Policies;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

use App\Models\User;

class ServicePolicy
{
    public function checkPermission(User $user)
    {
        return CheckPermissionWithPolicy($user,'Service')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
    public function delete(User $user){
        return CheckPermissionWithPolicy($user,'Service')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
