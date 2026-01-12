<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RvmMachine extends Model
{
    protected $fillable = [
        'name',
        'location',
        'serial_number',
        'status',
        'capacity_percentage',
        'last_ping'
    ];

    protected $casts = [
        'last_ping' => 'datetime',
    ];

    public function telemetry(): HasMany
    {
        return $this->hasMany(TelemetryData::class);
    }
}
