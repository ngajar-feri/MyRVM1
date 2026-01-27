<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\RvmMachine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateRvmApiKey Middleware
 * 
 * Validates API requests from RVM-Edge devices using X-RVM-API-KEY header.
 * The API key is matched against rvm_machines.api_key column.
 * 
 * Used for endpoints that require machine authentication but not user login,
 * such as handshake, telemetry, and heartbeat endpoints.
 * 
 * @package App\Http\Middleware
 */
class ValidateRvmApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-RVM-API-KEY');

        // 401 - Missing or empty API key
        if (empty($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kunci API Tidak Valid. Silakan import ulang kredensial.',
                'error_code' => 'MISSING_API_KEY',
            ], 401);
        }

        // Hash the incoming key to match storage (SHA-256)
        $hashedKey = hash('sha256', $apiKey);

        // Find Edge Device by API key
        $edgeDevice = \App\Models\EdgeDevice::where('api_key', $hashedKey)->first();

        // 401 - Invalid API key
        if (!$edgeDevice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kunci API Tidak Valid. Silakan import ulang kredensial.',
                'error_code' => 'INVALID_API_KEY',
            ], 401);
        }

        // Get associated RVM Machine
        $machine = $edgeDevice->rvmMachine;

        if (!$machine) {
             return response()->json([
                'status' => 'error',
                'message' => 'Edge Device tidak terhubung ke RVM Machine.',
                'error_code' => 'ORPHAN_DEVICE',
            ], 403);
        }

        // 403 - Machine is blocked/suspended
        if (in_array($machine->status, ['blocked', 'suspended'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesin ini diblokir oleh Server. Hubungi Admin.',
                'error_code' => 'MACHINE_BLOCKED',
            ], 403);
        }

        // Inject machine into request for controllers
        $request->attributes->set('rvm_machine', $machine);

        return $next($request);
    }
}
