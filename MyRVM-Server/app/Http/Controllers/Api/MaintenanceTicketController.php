<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket;
use App\Models\TechnicianAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * MaintenanceTicket API Controller
 * 
 * Business Rules:
 * - Only super_admin and admin can create tickets
 * - Assignee MUST exist in technician_assignments for the target RVM
 */
class MaintenanceTicketController extends Controller
{
    /**
     * Get all tickets with relationships
     */
    public function index(): JsonResponse
    {
        $tickets = MaintenanceTicket::with(['rvmMachine', 'reporter', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    /**
     * Create new ticket
     */
    public function store(Request $request): JsonResponse
    {
        $currentUser = Auth::user();

        // Guard: Only super_admin and admin can create
        if (!in_array($currentUser->role, ['super_admin', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Only Admin can create tickets.'
            ], 403);
        }

        $validated = $request->validate([
            'rvm_machine_id' => 'required|exists:rvm_machines,id',
            'category' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // If assignee is provided, validate they have access to this RVM
        if (!empty($validated['assignee_id'])) {
            $hasAccess = TechnicianAssignment::where('technician_id', $validated['assignee_id'])
                ->where('rvm_machine_id', $validated['rvm_machine_id'])
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Teknisi ini belum memiliki izin akses ke RVM ini. Silahkan tambahkan di menu Assignment terlebih dahulu.'
                ], 422);
            }
        }

        $ticket = MaintenanceTicket::create([
            'rvm_machine_id' => $validated['rvm_machine_id'],
            'created_by' => $currentUser->id,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => $validated['assignee_id'] ? 'assigned' : 'pending',
            'assigned_at' => $validated['assignee_id'] ? now() : null,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => $ticket->load(['rvmMachine', 'assignee'])
        ], 201);
    }

    /**
     * Get single ticket
     */
    public function show(int $id): JsonResponse
    {
        $ticket = MaintenanceTicket::with(['rvmMachine', 'reporter', 'assignee'])->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        return response()->json(['data' => $ticket]);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $ticket = MaintenanceTicket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);

        // Update timestamps based on status
        if ($validated['status'] === 'in_progress' && !$ticket->started_at) {
            $ticket->started_at = now();
        }
        if ($validated['status'] === 'resolved' && !$ticket->completed_at) {
            $ticket->completed_at = now();
            $ticket->resolution_notes = $validated['resolution_notes'] ?? null;
        }

        $ticket->status = $validated['status'];
        $ticket->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $ticket
        ]);
    }

    /**
     * Update ticket (full update)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $currentUser = Auth::user();

        // Guard: Only super_admin and admin can update
        if (!in_array($currentUser->role, ['super_admin', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Only Admin can update tickets.'
            ], 403);
        }

        $ticket = MaintenanceTicket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $validated = $request->validate([
            'category' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:pending,assigned,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);

        // If changing assignee, validate RVM access
        if (!empty($validated['assignee_id']) && $validated['assignee_id'] !== $ticket->assignee_id) {
            $hasAccess = TechnicianAssignment::where('technician_id', $validated['assignee_id'])
                ->where('rvm_machine_id', $ticket->rvm_machine_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Teknisi ini belum memiliki izin akses ke RVM ini.'
                ], 422);
            }

            $ticket->assignee_id = $validated['assignee_id'];
            $ticket->assigned_at = now();
            if ($ticket->status === 'pending') {
                $ticket->status = 'assigned';
            }
        }

        // Update other fields
        if (isset($validated['category']))
            $ticket->category = $validated['category'];
        if (isset($validated['priority']))
            $ticket->priority = $validated['priority'];
        if (isset($validated['description']))
            $ticket->description = $validated['description'];
        if (isset($validated['resolution_notes']))
            $ticket->resolution_notes = $validated['resolution_notes'];

        // Handle status changes with timestamps
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_progress' && !$ticket->started_at) {
                $ticket->started_at = now();
            }
            if ($validated['status'] === 'resolved' && !$ticket->completed_at) {
                $ticket->completed_at = now();
            }
            $ticket->status = $validated['status'];
        }

        $ticket->save();

        return response()->json([
            'message' => 'Ticket updated successfully',
            'data' => $ticket->load(['rvmMachine', 'assignee'])
        ]);
    }

    /**
     * Delete ticket
     */
    public function destroy(int $id): JsonResponse
    {
        $currentUser = Auth::user();

        // Guard: Only super_admin can delete tickets
        if ($currentUser->role !== 'super_admin') {
            return response()->json([
                'message' => 'Unauthorized. Only Super Admin can delete tickets.'
            ], 403);
        }

        $ticket = MaintenanceTicket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}
