<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdgeDevice extends Model
{
    protected $fillable = [
        'rvm_id',
        'device_serial',
        'tailscale_ip',
        'hardware_info',
        'status',
        'last_heartbeat',
        'ai_model_version',
        'firmware_version',
        'latitude',
        'longitude',
        'location_accuracy_meters',
        'location_source',
        'location_address',
        'location_last_updated',
        'api_key',
    ];

    protected $casts = [
        'last_heartbeat' => 'datetime',
        'location_last_updated' => 'datetime',
        'hardware_info' => 'array',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function rvmMachine(): BelongsTo
    {
        return $this->belongsTo(RvmMachine::class, 'rvm_id');
    }
}
