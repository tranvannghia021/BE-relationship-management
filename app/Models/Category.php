<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $connection='mysql';
    protected $table='categories';
    protected $fillable=[
        'name'
    ];

    /**
     * @return HasMany
     */
    public function relationships():HasMany{
        return $this->hasMany(Relationship::class);
    }
}
