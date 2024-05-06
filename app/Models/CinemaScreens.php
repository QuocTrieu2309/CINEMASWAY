<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CinemaScreens extends Model
{
    use HasFactory;
    protected $fillable=[
        'cinema_id',
        'screen_id',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function cinema(){
        return $this->belongsTo(Cinema::class);
    }
    public function screen(){
        return $this->belongsTo(Screen::class);
    }


}
