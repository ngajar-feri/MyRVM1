<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RvmMachine;
use App\Models\TechnicianAssignment;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class RvmMachineController extends Controller
{
    /**
     * Role hierarchy for assignment permissions.
     */
    private $roleHierarchy = [
        'super_admin' => 1,
        'admin' => 2,
        'operator' => 3,
        'teknisi' => 3,
        'tenant' => 4,
        'user' => 5
    ];

    /**
     * Roles allowed to view machines.
     */
    private $viewAllowedRoles = ['super_admin', 'admin', 'operator', 'teknisi'];

    /**
     * Roles allowed to create/edit machines.
     */
    private $editAllowedRoles = ['super_admin', 'admin'];

    /**
     * List RVM machines with role-based filtering.
     * 
     * @OA\Get(
     *      path="/api/v1/rvm-machines",
     *      operationId="getRvmMachines",
     *      tags={"RVM Machines"},
     *      summary="List RVM machines (role-based)",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="List of machines",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *          )
     *      ),
     *      @OA\Response(response=403, description="Access denied")
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user has permission to view
        if (!in_array($user->role, $this->viewAllowedRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Your role does not have permission to view RVM machines.'
            ], 403);
        }

        // Admin/SuperAdmin see all, operator/teknisi see assigned only
        if (in_array($user->role, ['super_admin', 'admin'])) {
            $machines = RvmMachine::with('edgeDevice')->get();
        } else {
            // Filter by assignment for operator/teknisi
            $assignedIds = TechnicianAssignment::where('technician_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->pluck('rvm_machine_id');
            $machines = RvmMachine::with('edgeDevice')
                ->whereIn('id', $assignedIds)->get();
        }

        ActivityLog::log('RVM', 'Read', "User {$user->name} accessed RVM machines list", $user->id);

        return response()->json([
            'status' => 'success',
            'data' => $machines
        ]);
    }

    /**
     * Create a new RVM machine (admin/super_admin only).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, $this->editAllowedRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only Super Admin and Admin can create machines.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:rvm_machines,serial_number',
            'status' => 'in:online,offline,maintenance,full_warning',
        ]);

        $machine = RvmMachine::create($request->all());

        ActivityLog::log('RVM', 'Create', "Machine '{$machine->name}' created by {$user->name}", $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'RVM berhasil ditambahkan',
            'data' => $machine
        ], 201);
    }

    /**
     * Get RVM machine details (with access check).
     * Includes Edge Device info and latest telemetry.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, $this->viewAllowedRoles)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $machine = RvmMachine::with([
            'edgeDevice',
            'edgeDevice.telemetry' => function ($query) {
                $query->orderBy('client_timestamp', 'desc')->limit(5);
            }
        ])->findOrFail($id);

        // Check assignment for operator/teknisi
        if (in_array($user->role, ['operator', 'teknisi'])) {
            $isAssigned = TechnicianAssignment::where('technician_id', $user->id)
                ->where('rvm_machine_id', $id)
                ->exists();
            if (!$isAssigned) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied. This machine is not assigned to you.'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $machine
        ]);
    }

    /**
     * Update RVM machine (admin/super_admin only).
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, $this->editAllowedRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only Super Admin and Admin can update machines.'
            ], 403);
        }

        $machine = RvmMachine::findOrFail($id);

        $request->validate([
            'name' => 'string|max:255',
            'location' => 'string|max:255',
            'serial_number' => 'string|unique:rvm_machines,serial_number,' . $id,
            'status' => 'in:online,offline,maintenance,full_warning',
        ]);

        $machine->update($request->all());

        ActivityLog::log('RVM', 'Update', "Machine '{$machine->name}' updated by {$user->name}", $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'RVM berhasil diperbarui',
            'data' => $machine
        ]);
    }

    /**
     * Assign machine to users (with hierarchy check).
     * 
     * @OA\Post(
     *      path="/api/v1/rvm-machines/{id}/assign",
     *      operationId="assignRvmMachine",
     *      tags={"RVM Machines"},
     *      summary="Assign machine to users",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"assignee_ids"},
     *              @OA\Property(property="assignee_ids", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="description", type="string")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Assignment successful"),
     *      @OA\Response(response=403, description="Not authorized to assign")
     * )
     */
    public function assignMachine(Request $request, $id)
    {
        $assigner = $request->user();

        // Only super_admin and admin can assign
        if (!in_array($assigner->role, ['super_admin', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authorized. Only Super Admin and Admin can assign machines.'
            ], 403);
        }

        $request->validate([
            'assignee_ids' => 'required|array',
            'assignee_ids.*' => 'exists:users,id',
            'description' => 'nullable|string|max:500'
        ]);

        $machine = RvmMachine::findOrFail($id);
        $assignedUsers = [];
        $skippedUsers = [];
        $assignerLevel = $this->roleHierarchy[$assigner->role];

        foreach ($request->assignee_ids as $assigneeId) {
            $assignee = User::find($assigneeId);

            if (!$assignee)
                continue;

            $assigneeLevel = $this->roleHierarchy[$assignee->role] ?? 5;

            // Check hierarchy: cannot assign to higher level role
            if ($assigneeLevel < $assignerLevel) {
                $skippedUsers[] = [
                    'id' => $assigneeId,
                    'name' => $assignee->name,
                    'reason' => "Cannot assign to higher role ({$assignee->role})"
                ];
                continue;
            }

            // Check if already assigned
            $existing = TechnicianAssignment::where('technician_id', $assigneeId)
                ->where('rvm_machine_id', $id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->first();

            if ($existing) {
                $skippedUsers[] = [
                    'id' => $assigneeId,
                    'name' => $assignee->name,
                    'reason' => 'Already assigned'
                ];
                continue;
            }

            // Create assignment
            TechnicianAssignment::create([
                'technician_id' => $assigneeId,
                'rvm_machine_id' => $id,
                'assigned_by' => $assigner->id,
                'status' => 'assigned',
                'description' => $request->description
            ]);

            $assignedUsers[] = [
                'id' => $assigneeId,
                'name' => $assignee->name,
                'role' => $assignee->role
            ];
        }

        ActivityLog::log(
            'RVM',
            'Assign',
            "Machine '{$machine->name}' assigned to " . count($assignedUsers) . " user(s) by {$assigner->name}",
            $assigner->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment completed',
            'data' => [
                'machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'assigned' => $assignedUsers,
                'skipped' => $skippedUsers
            ]
        ]);
    }

    /**
     * Get machine assignments.
     */
    public function getAssignments(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, $this->viewAllowedRoles)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $machine = RvmMachine::findOrFail($id);
        $assignments = TechnicianAssignment::where('rvm_machine_id', $id)
            ->with(['technician:id,name,email,role', 'assignedBy:id,name'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'machine' => $machine,
                'assignments' => $assignments
            ]
        ]);
    }
}
