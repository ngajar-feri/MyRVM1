<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RvmMachine;
use App\Models\EdgeDevice;
use App\Models\TelemetryData;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class EdgeDeviceController extends Controller
{
    /**
     * List all Edge devices.
     */
    public function index(Request $request)
    {
        $query = EdgeDevice::with('rvmMachine:id,serial_number,location,status');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $devices = $query->orderBy('updated_at', 'desc')->get();

        // Calculate stats
        $stats = [
            'total' => $devices->count(),
            'online' => $devices->where('status', 'online')->count(),
            'offline' => $devices->where('status', 'offline')->count(),
            'maintenance' => $devices->where('status', 'maintenance')->count(),
            'avg_cpu' => $this->calculateAverageMetric($devices, 'cpu_usage'),
            'avg_gpu' => $this->calculateAverageMetric($devices, 'gpu_usage'),
            'avg_temp' => $this->calculateAverageMetric($devices, 'temperature'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $devices,
            'stats' => $stats
        ]);
    }

    /**
     * Calculate average metric from health_metrics JSON.
     */
    private function calculateAverageMetric($devices, $field)
    {
        $values = $devices->filter(function ($d) use ($field) {
            return isset($d->health_metrics[$field]);
        })->pluck("health_metrics.$field");

        if ($values->isEmpty())
            return 0;
        return round($values->avg(), 1);
    }

    /**
     * Send telemetry data from Edge Device.
     * 
     * @OA\Post(
     *      path="/api/v1/devices/{id}/telemetry",
     *      operationId="sendTelemetry",
     *      tags={"Edge Device"},
     *      summary="Send telemetry data (weight, capacity)",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="RVM Machine ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"plastic_weight","aluminum_weight","total_items"},
     *              @OA\Property(property="plastic_weight", type="number", format="float", example=0.5),
     *              @OA\Property(property="aluminum_weight", type="number", format="float", example=0.2),
     *              @OA\Property(property="glass_weight", type="number", format="float", example=0.0),
     *              @OA\Property(property="total_items", type="integer", example=3),
     *              @OA\Property(property="battery_level", type="integer", example=85),
     *              @OA\Property(property="temperature", type="number", format="float", example=28.5)
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Telemetry recorded",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Telemetry received")
     *          )
     *      )
     * )
     */
    public function telemetry(Request $request, $id)
    {
        $machine = RvmMachine::findOrFail($id);

        $request->validate([
            'plastic_weight' => 'required|numeric|min:0',
            'aluminum_weight' => 'required|numeric|min:0',
            'glass_weight' => 'nullable|numeric|min:0',
            'total_items' => 'required|integer|min:0',
            'battery_level' => 'nullable|integer|min:0|max:100',
            'temperature' => 'nullable|numeric',
        ]);

        TelemetryData::create([
            'rvm_machine_id' => $id,
            'plastic_weight' => $request->plastic_weight,
            'aluminum_weight' => $request->aluminum_weight,
            'glass_weight' => $request->glass_weight ?? 0,
            'total_items' => $request->total_items,
            'battery_level' => $request->battery_level,
            'temperature' => $request->temperature,
        ]);

        // Update last ping machine status
        $machine->update(['last_ping' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Telemetry received',
        ], 201);
    }

    /**
     * Send heartbeat (Ping) with auto-discovery IP update.
     * 
     * @OA\Post(
     *      path="/api/v1/devices/{id}/heartbeat",
     *      operationId="sendHeartbeat",
     *      tags={"Edge Device"},
     *      summary="Send heartbeat to indicate device is online",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="ip_local", type="string", example="192.168.1.10"),
     *              @OA\Property(property="tailscale_ip", type="string", example="100.64.0.5"),
     *              @OA\Property(property="network_interfaces", type="object"),
     *              @OA\Property(property="health_metrics", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Heartbeat received"
     *      )
     * )
     */
    public function heartbeat(Request $request, $id)
    {
        // Update RVM Machine
        $machine = RvmMachine::findOrFail($id);
        $machine->update([
            'last_ping' => now(),
            'status' => 'online'
        ]);

        // Update EdgeDevice with auto-discovered IP addresses
        $edgeDevice = EdgeDevice::where('rvm_machine_id', $id)->first();
        if ($edgeDevice) {
            $updateData = [
                'status' => 'online',
                'updated_at' => now(),
            ];

            // Auto-update IP addresses from device report
            if ($request->has('ip_local')) {
                $updateData['ip_address_local'] = $request->ip_local;
            }
            if ($request->has('tailscale_ip')) {
                $updateData['tailscale_ip'] = $request->tailscale_ip;
            }
            if ($request->has('network_interfaces')) {
                $updateData['network_interfaces'] = $request->network_interfaces;
            }
            if ($request->has('health_metrics')) {
                $updateData['health_metrics'] = $request->health_metrics;
            }

            $edgeDevice->update($updateData);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Heartbeat received',
            'server_time' => now()->toIso8601String()
        ]);
    }

    /**
     * Register new Edge device.
     */
    public function register(Request $request)
    {
        $request->validate([
            'device_serial' => 'required|string|max:255|unique:edge_devices,device_id',
            'rvm_id' => 'nullable|exists:rvm_machines,id',
            'tailscale_ip' => 'nullable|ip',
            'hardware_info' => 'nullable|array',
            // New fields from 3-section form
            'location_name' => 'nullable|string|max:255',
            'inventory_code' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string',
            'status' => 'nullable|in:maintenance,inactive,offline',
            'ai_model_version' => 'nullable|string',
        ]);

        // Generate API key (shown only once)
        $apiKey = 'rvm_' . Str::random(60);
        $apiKeyHash = hash('sha256', $apiKey);

        // Extract hardware info
        $hardwareInfo = $request->hardware_info ?? [];

        // Create device using model
        $controllerType = $hardwareInfo['controller_type'] ?? 'NVIDIA Jetson';
        $device = EdgeDevice::create([
            'rvm_machine_id' => $request->rvm_id,
            'device_id' => $request->device_serial,
            'type' => $controllerType, // Required NOT NULL column
            'location_name' => $request->location_name,
            'inventory_code' => $request->inventory_code,
            'description' => $request->description,
            'tailscale_ip' => $request->tailscale_ip,
            'controller_type' => $controllerType,
            'camera_id' => $hardwareInfo['camera_id'] ?? null,
            'threshold_full' => $hardwareInfo['threshold_full'] ?? 90,
            'ai_model_version' => $request->ai_model_version ?? 'best.pt',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'status' => $request->status ?? 'maintenance',
            'api_key' => $apiKeyHash,
            'health_metrics' => [],
        ]);

        ActivityLog::log(
            'Edge',
            'Create',
            "Edge device {$request->device_serial} registered at {$request->location_name}",
            $request->user()?->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Device registered successfully',
            'data' => [
                'edge_device_id' => $device->id,
                'device_serial' => $device->device_id,
                'api_key' => $apiKey, // Only returned once, never stored in plain text
                'config' => [
                    'server_url' => config('app.url'),
                    'telemetry_interval_seconds' => 300,
                    'heartbeat_interval_seconds' => 60,
                    'model_sync_interval_minutes' => 30,
                    'threshold_full' => $device->threshold_full,
                ]
            ]
        ], 201);
    }

    /**
     * Check for model updates.
     */
    public function modelSync(Request $request)
    {
        $request->validate([
            'device_serial' => 'required|string',
            'current_version' => 'nullable|string',
            'model_name' => 'required|string',
        ]);

        // Get active model version
        $latestModel = \DB::table('ai_model_versions')
            ->where('model_name', $request->model_name)
            ->where('is_active', true)
            ->orderBy('deployed_at', 'desc')
            ->first();

        if (!$latestModel) {
            return response()->json([
                'status' => 'success',
                'update_available' => false,
                'message' => 'No active model found'
            ]);
        }

        $updateAvailable = $request->current_version !== $latestModel->version;

        if ($updateAvailable) {
            return response()->json([
                'status' => 'success',
                'update_available' => true,
                'data' => [
                    'model_name' => $latestModel->model_name,
                    'latest_version' => $latestModel->version,
                    'current_version' => $request->current_version,
                    'file_path' => $latestModel->file_path,
                    'file_size_mb' => $latestModel->file_size_mb,
                    'sha256_hash' => $latestModel->sha256_hash,
                    'download_url' => "/api/v1/edge/download-model/{$latestModel->sha256_hash}",
                    'deployed_at' => $latestModel->deployed_at
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'update_available' => false,
            'current_version' => $request->current_version
        ]);
    }

    /**
     * Update device location.
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'edge_device_id' => 'required|exists:edge_devices,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_source' => 'required|in:manual,gps_module',
            'accuracy_meters' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'updated_by_user_id' => 'nullable|exists:users,id',
        ]);

        \DB::table('edge_devices')
            ->where('id', $request->edge_device_id)
            ->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_accuracy_meters' => $request->accuracy_meters,
                'location_source' => $request->location_source,
                'location_address' => $request->address,
                'location_last_updated' => now(),
                'updated_at' => now()
            ]);

        ActivityLog::log('Edge', 'Update', "Edge device #{$request->edge_device_id} location updated", $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully'
        ]);
    }

    /**
     * Upload images to MinIO.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'original_image' => 'required|image|mimes:jpeg,jpg|max:5120', // 5MB
            'processed_image' => 'required|image|mimes:jpeg,jpg|max:5120',
            'mask_image' => 'nullable|image|mimes:png|max:1024', // 1MB
            'metadata' => 'required|json',
        ]);

        $metadata = json_decode($request->metadata, true);
        $sessionId = $metadata['session_id'] ?? 'unknown';
        $itemSequence = $metadata['item_sequence'] ?? 1;
        $date = now()->format('Y-m-d');

        // Storage paths
        $basePath = "images/{$date}/{$sessionId}";

        // Upload original image
        $originalPath = $request->file('original_image')
            ->storeAs("{$basePath}/raw", "item-{$itemSequence}-original.jpg", 'public');

        // Upload processed image
        $processedPath = $request->file('processed_image')
            ->storeAs("{$basePath}/processed", "item-{$itemSequence}-annotated.jpg", 'public');

        // Upload mask if provided
        $maskPath = null;
        if ($request->hasFile('mask_image')) {
            $maskPath = $request->file('mask_image')
                ->storeAs("{$basePath}/masks", "item-{$itemSequence}-mask.png", 'public');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'original_url' => \Storage::url($originalPath),
                'processed_url' => \Storage::url($processedPath),
                'mask_url' => $maskPath ? \Storage::url($maskPath) : null,
                'uploaded_at' => now()->toIso8601String()
            ]
        ], 201);
    }

    /**
     * Download device configuration as JSON file.
     * Uses Laravel's streamDownload for efficient on-the-fly generation.
     */
    public function downloadConfig($deviceId)
    {
        $device = EdgeDevice::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }

        // Build config data
        $configData = [
            'rvm_edge_config' => [
                'device_id' => $device->device_id,
                'api_key' => '', // API key not included for security (user copies it manually)
                'location_name' => $device->location_name ?? '',
                'server_url' => config('app.url'),
                'telemetry_interval_seconds' => 300,
                'heartbeat_interval_seconds' => 60,
                'model_sync_interval_minutes' => 30,
                'threshold_full' => $device->threshold_full ?? 90,
            ],
            'generated_at' => now()->toIso8601String(),
            'warning' => 'Keep this file secure. Add your API key manually.'
        ];

        // Sanitize filename
        $safeName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $device->device_id);
        $fileName = "rvm-config-{$safeName}.json";

        // Use streamDownload for efficient on-the-fly generation (no disk write)
        return response()->streamDownload(function () use ($configData) {
            echo json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }
}
