<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Cog\Contracts\Ban\Bannable as BannableInterface;
use Cog\Laravel\Ban\Traits\Bannable;

class User extends Authenticatable implements BannableInterface
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, Bannable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'avatar_img',
        'country',
        'gender',
        'is_banned',
        'is_approved',
        'manager_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    public function managedRoom(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function managedFloor(): HasMany
    {
        return $this->hasMany(Floor::class);
    }

    public function reservations(): HasMany // for clients
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    public function managedReservations(): HasMany // for receptionists
    {
        return $this->hasMany(Reservation::class, 'receptionist_id');
    }

    // self relation // check it
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function subOrdinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id', 'id');
    }
}
