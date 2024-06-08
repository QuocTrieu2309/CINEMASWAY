<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'quantity',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }

}
