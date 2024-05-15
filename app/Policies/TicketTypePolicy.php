<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class TicketTypePolicy
{
    public function checkPermission(User $user)
    {   
        return CheckPermissionWithPolicy($user,'Ticket-Type')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
    public function delete(User $user){
        return CheckPermissionWithPolicy($user,'Ticket-Type')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
