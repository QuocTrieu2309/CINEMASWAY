<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class TranslationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function checkPermission(User $user)
    {   
        return CheckPermissionWithPolicy($user,'Translation')
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập', HttpResponse::HTTP_FORBIDDEN);
    }
}
