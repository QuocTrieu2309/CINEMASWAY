<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;
    const STATUS_AVAILABLE = 'Available'; // Còn trống
    const STATUS_HELD = 'Held';           // Đang bị giữ
    const STATUS_SELECTED = 'Selected';   // Đang chọn
    const STATUS_RESERVED = 'Reserved';   // Đã đặt
    const STATUS_PAID = 'Paid';           // Đã thanh toán
    protected $fillable = [
        'booking_id',
        'showtime_id',
        'seat_id',
        'code',
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
