<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;
    const STATUS_CURRENTLY = 'Currently Showing';
    const STATUS_COMING = 'Coming Soon';
    const STATUS_STOPPED = 'Stopped Showing';
    const RATED_P = 'P';
    const RATED_C13 = 'C13';
    const RATED_C16 = 'C16';
    const RATED_C18 = 'C18';
    protected $fillable = [
        'title',
        'genre',
        'director',
        'actor',
        'duration',
        'release_date',
        'status',
        'rated',
        'like',
        'description',
        'image',
        'trailer',
        'deleted',
        'end_date'
   ];
   protected $hidden = [
       'created_by',
       'updated_by',
       'created_at',
       'updated_at',
   ];

   public function showtimes(){
    return $this->hasMany(Showtime::class);
   }
}
