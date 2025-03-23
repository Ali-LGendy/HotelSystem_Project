<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = 
    [
        'stripe_payment_id', 
        'amount',
        'status',
        'reservation_id',
    ];

    public function reservation(): HasMany
    {
        return $this->hasMany(Reservation::class, 'reservation_id');
    }
}
