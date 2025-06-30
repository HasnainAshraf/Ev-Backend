<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activePorts()
    {
        return $this->ports()->where('is_active', true);
    }
} 