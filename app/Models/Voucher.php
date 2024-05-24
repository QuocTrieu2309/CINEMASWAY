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

    protected $fillable = [
        'user_id',
        'code',
        'pin',
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
