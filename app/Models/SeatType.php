<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatType extends Model
{
    use HasFactory;
    protected $fillable = [
        'screen_id',
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
    public function screen(){
        return $this->belongsTo(Screen::class);
    }
}
