<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = "bookings";
    protected $primaryKey = "id";
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'showtime_id',
        'code',
        'quantity',
        'subtotal',
        'status',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted',
        'created_at',
        'updated_at',
    ];
  
    public function user()
    {
        return $this->belongsTo(User::class);
    }
  
    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }
}
