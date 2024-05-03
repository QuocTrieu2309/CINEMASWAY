<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;
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
        'deleted'
   ];
   protected $hidden = [
       'created_by',
       'updated_by',
       'created_at',
       'updated_at',
   ];
}
