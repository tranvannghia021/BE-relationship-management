<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;
    protected $connection='pgsql';
    protected $table='appointment';
    protected $fillable=[
        'relationship_ids',
        'type',
        'user_id',
        'address',
        'name',
        'notes',
        'date_meeting'
    ];
    protected $casts=[
        'time'=>'datetime',
        'relationship_ids'=>'array'
    ];

}
