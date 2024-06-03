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
        'ticket_type_id',
        'showtime_id',
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
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
