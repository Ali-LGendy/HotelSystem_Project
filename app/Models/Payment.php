<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = 
    [
        'stripe_payment_id', 
        'amount',
        'status',
        'reservation_id',
    ];

    public function reservation(): hasMany
    {
        return $this->hasMany(Reservation::class, 'reservation_id');
    }
}
