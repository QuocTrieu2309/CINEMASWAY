<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "ticket_types";
    protected $primaryKey = "id";
    public $timestamps = true;
    protected $fillable = [
        'seat_type_id',
        'name',
        'price',
        'promotion_price',
        'created_by',
        'updated_by'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function seattypes()
    {
        return $this->hasMany(SeatType::class, 'seat_type_id', 'id');
    }
}
