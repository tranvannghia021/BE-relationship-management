<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Relationship extends Model
{
    use HasFactory;
    protected $connection='mysql';
    protected $table='relationships';
    protected $fillable=[
        'user_id',
        'relationship_id',
        'category_id',
        'first_name',
        'last_name',
        'avatar',
        'birthday',
        'phone',
        'point',
        'gender',
        'last_meeting',
        'profiles',
        'notes'
    ];
    protected $casts=[
        'birthday'=>'datetime',
        'last_meeting'=>'datetime'
    ];

    /**
     * @return BelongsTo
     */
    public function users() :BelongsTo{
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function categories() :BelongsTo{
        return $this->belongsTo(Relationship::class,'category_id');
    }

    /**
     * @return HasMany
     */
    public function appointment():HasMany{
        return $this->hasMany(Appointment::class);
    }
}
