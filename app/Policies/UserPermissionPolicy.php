<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\UserPermission;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class UserPermissionPolicy
{
    public function checkPermission(User $user)
    {   
        return CheckPermissionWithPolicy($user,'UserPermission')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}