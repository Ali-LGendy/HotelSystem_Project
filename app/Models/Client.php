<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'country',
        'gender',
        'approved',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}