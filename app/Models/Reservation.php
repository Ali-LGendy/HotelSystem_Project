<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = 
    [
        'accompany_number',
        'price_paid',
        'check_in_date',
        'check_out_date',
        'status',
        'client_id',
        'room_id',
        'receptionist_id',
    ];

    public function client(): belongsTo
    {
        return $this->belongsTo(User::class,'client_id');
    }

    public function room(): hasMany
    {
        return $this->hasMany(Room::class, 'room_id');
    }

    public function receptionist(): belongsTo
    {
        return $this->belongsTo(User::class,'receptionist_id');
    }

    public function payment(): BelongsTo
    {
        return $this->BelongsTo(Payment::class);
    }
}
