<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;
    const STATUS_EMPTYSEAT = 'Ghế trống';
    const STATUS_BELINGHOLD = 'Ghế đang được giữ';
    const STATUS_SELECTED = 'Ghế Đang chọn';
    const STATUS_SOLD = 'Ghế đã bán';
    const STATUS_RESERVED = 'Ghế đã đặt trước';
    protected $fillable =[
        'cinema_screens_id',
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
    public function cinemaScreen(){
        return $this->hasMany(CinemaScreen::class);
    }
    public function seatType(){
        return $this->hasOne(seatType::class);
    }


}
