<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'permission_id',
        'deleted'
   ];
   protected $hidden = [
       'created_by',
       'updated_by',
       'created_at',
       'updated_at',
   ];
}
