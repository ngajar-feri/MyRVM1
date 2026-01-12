# API Improvements Plan - Logging, Roles, Access Control

**Versi Dokumen**: 1.0  
**Tanggal Revisi**: Sabtu-11 Januari 2026 - 03:27 PM  
**Tujuan**: Memperbaiki 3 issues pada API untuk meningkatkan security dan auditability:
1. Integrasi Activity Logging ke semua endpoint critical
2. Menyamakan akses level Operator dan Teknisi
3. Proteksi RVM Machines endpoint dengan role + assignment-based access

**Status**: Menunggu Approval

---

## Problem Summary

| No | Issue | Current State | Target State |
|----|-------|---------------|--------------|
| 1 | **Activity Logging** | Hanya Auth controllers yang ter-log | Semua endpoint critical wajib ter-log |
| 2 | **Operator/Teknisi Roles** | Akses level berbeda | Akses level sama |
| 3 | **RVM Machines Access** | Public (tanpa auth) | Protected + filter by assignment |

---

## Issue #1: Activity Logging

### Current State
```
ActivityLog::log() hanya ada di:
├── Auth/LoginController.php (login, logout)
└── Api/AuthController.php (register, login, logout)
```

### Changes Required

| Controller | Methods | Module | Actions |
|------------|---------|--------|---------|
| TransactionController | start, commit, cancel | Transaction | Create, Complete, Cancel |
| RvmMachineController | store, update | RVM | Create, Update |
| TechnicianController | generatePin, validatePin | Maintenance | Create, Access |
| RedemptionController | redeem | Redemption | Redeem |
| TenantVoucherController | store, update, destroy | Voucher | Create, Update, Delete |
| EdgeDeviceController | register | Edge | Create |
| UserController | updateProfile, changePassword | User | Update, Security |

---

## Issue #2: Role Unification (Operator = Teknisi)

### Current Documentation (Incorrect)
```
| operator | Machine management |
| teknisi  | Maintenance tasks  |
```

### Target Documentation
```
| operator | Machine & Maintenance access (assigned) |
| teknisi  | Machine & Maintenance access (assigned) |
```

### Access Level
Both roles can:
- View `/v1/technician/assignments` (their own)
- POST `/v1/technician/generate-pin`
- View `/v1/rvm-machines` (only assigned)

---

## Issue #3: RVM Machines Access Control

### Current Routes (Public - INSECURE)
```php
// routes/api.php
Route::get('/v1/rvm-machines', [RvmMachineController::class, 'apiIndex']);
Route::get('/v1/rvm-machines/{id}', [RvmMachineController::class, 'show']);
```

### Target Access Matrix

| Role | GET /rvm-machines | GET /rvm-machines/{id} |
|------|-------------------|------------------------|
| super_admin | ✅ All | ✅ All |
| admin | ✅ All | ✅ All |
| operator | ✅ Assigned only | ✅ Assigned only |
| teknisi | ✅ Assigned only | ✅ Assigned only |
| user | ❌ 403 | ❌ 403 |
| tenant | ❌ 403 | ❌ 403 |

### Implementation Logic
```php
public function index(Request $request)
{
    $user = $request->user();
    $allowedRoles = ['super_admin', 'admin', 'operator', 'teknisi'];
    
    if (!in_array($user->role, $allowedRoles)) {
        return response()->json(['message' => 'Access denied'], 403);
    }
    
    if (in_array($user->role, ['operator', 'teknisi'])) {
        // Filter by assignment
        $assignedIds = TechnicianAssignment::where('technician_id', $user->id)
            ->pluck('rvm_machine_id');
        $machines = RvmMachine::whereIn('id', $assignedIds)->get();
    } else {
        // Admin/SuperAdmin see all
        $machines = RvmMachine::all();
    }
    
    ActivityLog::log('RVM', 'Read', "User accessed RVM machines list", $user->id);
    return response()->json(['data' => $machines]);
}
```

---

## Files to Modify

| File | Changes |
|------|---------|
| `routes/api.php` | Move RVM routes from public to protected |
| `Api/RvmMachineController.php` | +Role check, +Assignment filter, +ActivityLog |
| `Api/TransactionController.php` | +ActivityLog to critical methods |
| `Api/TechnicianController.php` | +ActivityLog, +operator role support |
| `Api/RedemptionController.php` | +ActivityLog |
| `Api/TenantVoucherController.php` | +ActivityLog |
| `Api/EdgeDeviceController.php` | +ActivityLog |
| `Api/UserController.php` | +ActivityLog |
| Docs/api-endpoints-summary.md | Update role descriptions |

---

## Verification Plan

### Test Cases

| Test | Action | Expected Result |
|------|--------|-----------------|
| Logging | Create transaction | Log entry created |
| Logging | Redeem voucher | Log entry created |
| Role | Operator access assignments | Success |
| Role | Teknisi access assignments | Success |
| Access | Operator access RVM list | Only assigned machines |
| Access | User access RVM list | 403 Forbidden |
| Access | Admin access RVM list | All machines |

---

## Rollback Plan

1. Revert routes/api.php (restore public RVM routes)
2. Revert controller changes (remove ActivityLog, role checks)
3. Clear cache: `php artisan cache:clear`

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 11 Jan 2026 3:27 PM | Initial plan |
