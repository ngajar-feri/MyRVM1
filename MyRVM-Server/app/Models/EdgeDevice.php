<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EdgeDevice extends Model
{
    // Note: Actual table has columns from old migration (2026_01_08)
    // device_id, rvm_machine_id, type, ip_address, firmware_version, status, health_metrics
    protected $fillable = [
        'device_id',        // Unique identifier (MAC or custom)
        'rvm_machine_id',   // FK to rvm_machines
        'type',             // jetson, microcontroller, camera, etc.
        'ip_address',
        'firmware_version',
        'status',
        'health_metrics',   // JSON: CPU, RAM, Temp
    ];

    protected $casts = [
        'health_metrics' => 'array',
    ];

    /**
     * Get the RVM machine this edge device is installed in.
     */
    public function rvmMachine(): BelongsTo
    {
        return $this->belongsTo(RvmMachine::class, 'rvm_machine_id');
    }

    /**
     * Get telemetry records for this edge device.
     */
    public function telemetry(): HasMany
    {
        return $this->hasMany(EdgeTelemetry::class);
    }
}

