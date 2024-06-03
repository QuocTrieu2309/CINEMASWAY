<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    use HasFactory;
    const STATUS_ACTIVE = 'Kích hoạt';
    const STATUS_INACTIVE = 'Chưa kích hoạt';
    protected $fillable = [
        'user_id',
        'voucher_id',
        'pin',
        'status',
    ];
    protected $hidden = [
        'deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
}
