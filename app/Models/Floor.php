<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;

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
