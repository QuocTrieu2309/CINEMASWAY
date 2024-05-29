<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class VoucherPolicy
{
/**
     * check permissions user
     *
     * @param User $user
     * @return Response
     */
    public function checkPermission(User $user)
    {
        return CheckPermissionWithPolicy($user,'Transaction')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }

    /**
     * check permissions user when deleting
     *
     * @param User $user
     * @return Response
     */
    public function delete(User $user){
        return CheckPermissionWithPolicy($user,'Transaction')
        ? Response::allow()
        : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
