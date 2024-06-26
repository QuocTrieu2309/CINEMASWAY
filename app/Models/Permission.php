<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'deleted'
   ];
   protected $hidden = [
       'created_by',
       'updated_by',
       'created_at',
       'updated_at',
   ];
   public function userPermissions(){
    return $this->hasMany(UserPermission::class);
   }
}
