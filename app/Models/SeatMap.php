<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatMap extends Model
{
    use HasFactory;
    protected $fillable = [
        'cinema_screen_id',
        'seat_total',
        'total_row',
        'total_column',
        'layout',
        'deleted'
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
}
