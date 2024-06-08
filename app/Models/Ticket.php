<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'booking_id',
        'seat_number',
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
    public function booking(){
        return $this->belongsTo(Booking::class);
    }

    /**
     *
     * @return BelongsTo
     */
    public function showtime(){
        return $this->belongsTo(Showtime::class);
    }

    /**
     *
     * @return BelongsTo
     */
    public function seat(){
        return $this->belongsTo(Seat::class);
    }
}
