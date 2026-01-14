<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    /**
     * Get the edge device installed in this RVM machine.
     * Relationship: 1:1 (One RVM Machine has one Edge Device)
     */
    public function edgeDevice(): HasOne
    {
        return $this->hasOne(EdgeDevice::class, 'rvm_machine_id');
    }

    /**
     * Get telemetry data for this machine.
     */
    public function telemetry(): HasMany
    {
        return $this->hasMany(TelemetryData::class);
    }

    /**
     * Get edge telemetry through edge device.
     */
    public function edgeTelemetry(): HasManyThrough
    {
        return $this->hasManyThrough(
            EdgeTelemetry::class,
            EdgeDevice::class,
            'rvm_machine_id',  // FK on edge_devices
            'edge_device_id', // FK on edge_telemetry
            'id',             // PK on rvm_machines
            'id'              // PK on edge_devices
        );
    }
}

