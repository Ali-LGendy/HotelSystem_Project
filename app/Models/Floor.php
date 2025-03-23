<?php

namespace App\Models;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'manager_id',
    ];

    public function room(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! DONT DELETE THIS COMMENT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    /* test auto generated floor number
    protected $fillable = ['name', 'manager_id']; // Ensure 'number' is not fillable to prevent manual override

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($floor) {
            $latestFloor = Floor::orderByDesc('number')->first();
            $floor->number = $latestFloor ? $latestFloor->number + 1 : 1000; // Start from 1000
        });
    }*/
}
