<?php

namespace App\Models;

use App\Policies\ServicePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    use HasFactory;
    protected $fillable = [
        'booking_id',
        'service_id',
        'quantity',
        'subtotal',

    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    // public function bookings()
    // {
    //     return $this->belongsTo(Booking::class);
    // }
    public function services()
    {
        return $this->belongsTo(ServicePolicy::class);
    }
}
