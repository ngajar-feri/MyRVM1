<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * Update user profile.
     * 
     * @OA\Put(
     *      path="/api/v1/profile",
     *      operationId="updateProfile",
     *      tags={"User"},
     *      summary="Update user profile information",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email"},
     *              @OA\Property(property="name", type="string", example="Jane Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="jane@example.com")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Profile updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Profil berhasil diperbarui"),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        ActivityLog::log('User', 'Update', "User {$user->name} updated profile", $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }

    /**
     * Change password.
     * 
     * @OA\Put(
     *      path="/api/v1/change-password",
     *      operationId="changePassword",
     *      tags={"User"},
     *      summary="Change user password",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"current_password","new_password","new_password_confirmation"},
     *              @OA\Property(property="current_password", type="string", format="password", example="oldsecret"),
     *              @OA\Property(property="new_password", type="string", format="password", example="newsecret"),
     *              @OA\Property(property="new_password_confirmation", type="string", format="password", example="newsecret")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Password changed successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Password berhasil diubah")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password saat ini salah',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLog::log('User', 'Security', "User {$user->name} changed password", $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah',
        ]);
    }

    /**
     * Get user balance.
     */
    public function balance(Request $request)
    {
        $user = $request->user();

        // Calculate total earned
        $totalEarned = \DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_points') ?? 0;

        // Calculate total redeemed (join user_vouchers with vouchers to get points_required)
        $totalRedeemed = \DB::table('user_vouchers')
            ->join('vouchers', 'user_vouchers.voucher_id', '=', 'vouchers.id')
            ->where('user_vouchers.user_id', $user->id)
            ->sum('vouchers.points_required') ?? 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'points_balance' => $user->points_balance ?? 0,
                'tier' => 'silver', // TODO: Implement tier logic
                'total_earned' => $totalEarned,
                'total_redeemed' => $totalRedeemed
            ]
        ]);
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:2048', // 2MB
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->photo_url) {
            $oldPath = str_replace('/storage/', '', $user->photo_url);
            \Storage::disk('public')->delete($oldPath);
        }

        // Upload new photo
        $path = $request->file('photo')
            ->store('profile-photos', 'public');

        $user->update([
            'photo_url' => \Storage::url($path)
        ]);

        ActivityLog::log('User', 'Update', "User {$user->name} uploaded profile photo", $user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile photo uploaded',
            'data' => [
                'photo_url' => $user->photo_url
            ]
        ]);
    }

    /**
     * Get all users for admin dashboard.
     */
    public function getAllUsers(Request $request)
    {
        $users = \App\Models\User::select('id', 'name', 'email', 'role', 'points_balance', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Get user statistics for dashboard.
     */
    public function getUserStats($id)
    {
        try {
            $user = \App\Models\User::findOrFail($id);

            // Calculate stats
            $totalTransactions = \DB::table('transactions')
                ->where('user_id', $id)
                ->where('status', 'completed')
                ->count();

            $totalPoints = \DB::table('transactions')
                ->where('user_id', $id)
                ->where('status', 'completed')
                ->sum('total_points') ?? 0;

            // Calculate total redeemed (join user_vouchers with vouchers to get points_required)
            $totalRedeemed = \DB::table('user_vouchers')
                ->join('vouchers', 'user_vouchers.voucher_id', '=', 'vouchers.id')
                ->where('user_vouchers.user_id', $id)
                ->sum('vouchers.points_required') ?? 0;

            // Points history (last 7 days)
            $pointsHistory = \DB::table('transactions')
                ->where('user_id', $id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(7))
                ->select(
                    \DB::raw('DATE(created_at) as date'),
                    \DB::raw('SUM(total_points) as points')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'stats' => [
                    'total_transactions' => $totalTransactions,
                    'total_earned' => $totalPoints,
                    'total_redeemed' => $totalRedeemed,
                    'current_balance' => $user->points_balance ?? 0,
                    'points_history' => $pointsHistory
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load user stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user (Admin only).
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin,super_admin,operator,teknisi,tenan',
            'points_balance' => 'nullable|integer|min:0',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'points_balance' => $request->points_balance ?? 0,
        ]);

        ActivityLog::log('User', 'Create', "Admin {$request->user()->name} created user: {$user->name} ({$user->email})", $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update user (Admin only).
     */
    public function updateUserAdmin(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $id,
            'role' => 'required|string|in:user,admin,super_admin,operator,teknisi,tenan',
            'points_balance' => 'nullable|integer|min:0',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'points_balance' => $request->points_balance ?? $user->points_balance,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        ActivityLog::log('User', 'Update', "Admin {$request->user()->name} updated user: {$user->name} ({$user->email})", $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete user permanently (Admin only).
     */
    public function deleteUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        // Hard delete
        $user->delete();

        ActivityLog::log('User', 'Delete', "Admin {$request->user()->name} deleted user: {$userName} ({$userEmail})", $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }
}
