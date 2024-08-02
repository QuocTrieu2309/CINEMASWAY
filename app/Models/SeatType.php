<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatType extends Model
{
    use HasFactory;
    const SEAT_TYPE_N_2D = 'Ghế thường - 2D';
    const SEAT_TYPE_V_2D = 'Ghế vip - 2D';
    const SEAT_TYPE_C_2D = 'Ghế đôi - 2D';
    const SEAT_TYPE_N_3D = 'Ghế thường - 3D';
    const SEAT_TYPE_V_3D = 'Ghế vip - 3D';
    const SEAT_TYPE_C_3D = 'Ghế đôi - 3D';
    const SEAT_TYPE_N_4D = 'Ghế thường - 4D';
    const SEAT_TYPE_V_4D = 'Ghế vip - 4D';
    const SEAT_TYPE_C_4D = 'Ghế đôi - 4D';
    protected $fillable = [
        'name',
        'price',
        'promotion_price',
        'deleted'
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    public function seats(){
        return $this->hasMany(Seat::class);
    }
    public function screen() {
        return $this->belongsTo(Screen::class);
    }
}
