<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;


    protected $fillable = [
        'booking_id',
        'subtotal',
        'payment_method',
        'status',
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
    // public function booking(){
    //     return $this->belongsTo(Booking::class);
    // }
}
