<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'Active';

    const STATUS_EXPIRED = 'Expired';
    const TYPE_BIRTHDAY = 'Birthday';
    const TYPE_MEMBER_NORMAL = 'Member normal';
    const TYPE_MEMBER_VIP = 'Member vip';
    const TYPE_MEMBER_PREMIUM = 'Member premium';
    const TYPE_HOLIDAY = 'Holiday';
    protected $fillable = [
        'code',
        'type',
        'value',
        'start_date',
        'end_date',
        'status',
        'description',
        'deleted'
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    /**
     *
     * @return BelongsTo
     */
    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }
}
