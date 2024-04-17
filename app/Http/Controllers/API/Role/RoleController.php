<?php

namespace App\Http\Controllers\API\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //GET api/dashboard/role
    public function index(){
        
    }
}
