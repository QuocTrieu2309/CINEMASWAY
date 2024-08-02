<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;
    const STATUS_OCCUPIED = 'OCCUPIED';
    const STATUS_UNOCCUPIED = 'UNOCCUPIED';
    protected $fillable = [
        'cinema_screen_id',
        'seat_type_id',
        'seat_number',
        'status',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    public function cinemaScreen()
    {
        return $this->belongsTo(CinemaScreen::class);
    }
    public function seatType()
    {
        return $this->belongsTo(SeatType::class);
    }
    public function seatShowtime()
    {
        return $this->hasOne(SeatShowtime::class);
    }
        public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }
}
