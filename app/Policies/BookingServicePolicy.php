<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class BookingServicePolicy
{
    /**
     * Create a new policy instance.
     */
    public function checkPermission(User $user)
    {
        return CheckPermissionWithPolicy($user, 'BookingService')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
    public function delete(User $user)
    {
        return CheckPermissionWithPolicy($user, 'BookingService')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
