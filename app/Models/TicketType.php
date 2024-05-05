<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;
    protected $table = "ticket_types";
    protected $primaryKey = "id";
    public $timestamps = true;
    protected $fillable = [
        'seat_type_id',
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

    public function seatTypes()
    {
        return $this->hasMany(SeatType::class, 'seat_type_id', 'id');
    }
}
