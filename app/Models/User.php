<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    const GENDER_MALE = 'Nam';
    const GENDER_FEMALE = 'Nữ';
    const STATUS_ACTIVE = 'Kích hoạt';
    const STATUS_INACTIVE = 'Chưa kích hoạt';
    protected $fillable = [
        'role_id',
        'full_name',
        'phone',
        'email',
        'password',
        'gender',
        'birth_date',
        'avatar',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function userPermissions(){
        return $this->hasMany(UserPermission::class);
    }
    public function role(){
        return $this->belongsTo(Role::class);
    }
    public function permission(){
        return $this->hasManyThrough(Permission::class, UserPermission::class, 'user_id', 'id', 'id', 'permission_id');
    }
}
