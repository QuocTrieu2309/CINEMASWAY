<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    use HasFactory;
    protected $table = "showtimes";
    protected $primaryKey = "id";
    public $timestamps = true;
    const SUBTITLE_EN_VN ='Tiếng Anh-Phụ đề tiếng Việt';
    const SUBTITLE_EN_EN ='Tiếng Anh-Phụ đề tiếng Anh';
    const SUBTITLE_VN = 'Tiếng Việt';
    const STATUS_ACTIVE = 'Hoạt động';
    const STATUS_INACTIVE= 'Hủy bỏ';
    const STATUS_SOLD_OUT = 'Bán hết';
    const STATUS_AVAILABLE = 'Còn chỗ';
    const STATUS_COMPLETED = 'Kết thúc';

    const STATUS_EARLY = 'Suất chiếu sớm';
    protected $fillable = [
        'movie_id',
        'cinema_screen_id',
        'subtitle',
        'show_date',
        'show_time',
        'status',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted',
        'created_at',
        'updated_at',
    ];
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
    public function cinemaScreen()
    {
        return $this->belongsTo(CinemaScreen::class);
    }

    public function seatShowtime(){
        return $this->hasOne(SeatShowtime::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class);
    }

}
