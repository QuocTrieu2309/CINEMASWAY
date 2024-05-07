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
    protected $fillable = [
        'movie_id',
        'cinema_screen_id',
        'translation_id',
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
        return $this->belongsTo(CinemaScreens::class);
    }
    // public function translation()
    // {
    //     return $this->belongsTo(Translation::class);
    // }
}
