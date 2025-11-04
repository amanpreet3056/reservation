<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guest extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'location',
        'last_reservation_at',
        'password',
        'marketing_opt_in',
        'preferences',
    ];

    protected $casts = [
        'last_reservation_at' => 'datetime',
        'preferences' => 'array',
        'marketing_opt_in' => 'boolean',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name . ' ' . $this->last_name));
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}

