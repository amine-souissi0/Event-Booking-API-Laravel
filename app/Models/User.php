<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Roles used in the app */
    public const ROLE_ADMIN     = 'admin';
    public const ROLE_ORGANIZER = 'organizer';
    public const ROLE_CUSTOMER  = 'customer';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',   // NEW
        'role',    // NEW
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Always hash the password when set.
     */
    public function setPasswordAttribute($value): void
    {
        if ($value && strlen($value) < 60) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /* ===== Relationships (we'll use these soon) ===== */

    public function events()
    {
        // events this user created as an organizer
        return $this->hasMany(Event::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        // payments through the user's bookings
        return $this->hasManyThrough(Payment::class, Booking::class);
    }
}
