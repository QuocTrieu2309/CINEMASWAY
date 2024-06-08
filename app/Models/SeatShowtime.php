<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatShowtime extends Model
{
    use HasFactory;
    const STATUS_AVAILABLE = 'Available'; // Còn trống
    const STATUS_HELD = 'Held';           // Đang bị giữ
    const STATUS_SELECTED = 'Selected';   // Đang chọn
    const STATUS_RESERVED = 'Reserved';   // Đã đặt
    protected $fillable = [
        'user_id',
        'seat_id',
        'showtime_id',
        'status',
        'deleted'
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }
}
