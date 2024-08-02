<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function cinemaScreens(){
        return $this->hasMany(CinemaScreen::class);
    }

    public function seatTypes(){
        return $this->hasMany(SeatType::class);
    }
}
