# API Improvements Plan - Revision 1.1

**Versi Dokumen**: 1.1  
**Tanggal Revisi**: Sabtu-11 Januari 2026 - 03:38 PM  
**Referensi Dokumen Sebelumnya**: [AG1]-api-improvements-logging-roles-access-Sabtu-11012026-1527.md  
**Tujuan**: Revisi plan dengan penambahan hierarchical assignment model  
**Status**: Menunggu Approval

---

## Perubahan dari Versi 1.0

> Berdasarkan feedback user, ditambahkan:
> 1. **Hierarchical Assignment** - Super Admin/Admin bisa assign berdasarkan hierarki
> 2. **Multi-User Assignments** - Satu machine bisa di-assign ke banyak user
> 3. **Self-Assignment** - Super Admin/Admin bisa assign ke diri sendiri

---

## 1. Role Hierarchy (Tingkatan)

```
Level 1: super_admin  (tertinggi)
Level 2: admin
Level 3: teknisi / operator  (setara)
Level 4: tenant
Level 5: user  (terendah)
```

---

## 2. Assignment Rules (Aturan Penugasan)

### Siapa Bisa Assign?

| Assigner | Bisa Assign Ke | Tidak Bisa Assign Ke |
|----------|----------------|----------------------|
| super_admin | super_admin, admin, teknisi, operator | - |
| admin | admin, teknisi, operator | ❌ super_admin |
| teknisi/operator | ❌ Tidak bisa assign | - |
| tenant/user | ❌ Tidak bisa assign | - |

### Contoh Skenario

1. **Super Admin A** bisa assign machine ke:
   - Super Admin B ✅
   - Admin C ✅
   - Teknisi D ✅
   - Dirinya sendiri ✅

2. **Admin A** bisa assign machine ke:
   - Admin B ✅
   - Teknisi C ✅
   - Dirinya sendiri ✅
   - Super Admin ❌ (hierarki lebih tinggi)

3. **Multi-user assignment**:
   - Machine RVM-001 bisa di-assign ke: 1 Super Admin + 2 Admin + 3 Teknisi

---

## 3. RVM Machines Access Matrix (Revisi)

| Role | View List | View Detail | Create/Edit | Assign |
|------|-----------|-------------|-------------|--------|
| super_admin | ✅ All | ✅ All | ✅ | ✅ (≤ level) |
| admin | ✅ All | ✅ All | ✅ | ✅ (≤ level) |
| operator | ✅ Assigned | ✅ Assigned | ❌ | ❌ |
| teknisi | ✅ Assigned | ✅ Assigned | ❌ | ❌ |
| tenant | ❌ 403 | ❌ 403 | ❌ | ❌ |
| user | ❌ 403 | ❌ 403 | ❌ | ❌ |

---

## 4. New Endpoint: Assign Machine

### `POST /api/v1/rvm-machines/{id}/assign`

**Request Body:**
```json
{
  "assignee_ids": [2, 5, 8],
  "description": "Maintenance tugas mingguan"
}
```

**Response (Success):**
```json
{
  "status": "success",
  "message": "Assigned to 3 users",
  "data": {
    "machine_id": 1,
    "assignees": [
      {"id": 2, "name": "Admin 1", "role": "admin"},
      {"id": 5, "name": "Teknisi 1", "role": "teknisi"},
      {"id": 8, "name": "Operator 1", "role": "operator"}
    ]
  }
}
```

---

## 5. Database Schema Changes

### Modify: `technician_assignments`

| Column | Type | Description |
|--------|------|-------------|
| `assigned_by` | foreignId (users) | ID user yang melakukan assign |

```php
// Migration
$table->foreignId('assigned_by')->nullable()->constrained('users');
```

---

## 6. Files to Modify

| File | Changes |
|------|---------|
| `routes/api.php` | +POST assign endpoint |
| `RvmMachineController.php` | +assignMachine(), +hierarchy check |
| `TechnicianAssignment.php` | +assigned_by, +assignedBy() |
| Migration | +assigned_by column |

---

## 7. Verification Plan

| Test | Action | Expected |
|------|--------|----------|
| Hierarchy | Super Admin assign to Admin | ✅ Success |
| Hierarchy | Admin assign to Super Admin | ❌ 403 Forbidden |
| Multi-user | Assign to 3 users | ✅ 3 assignments created |
| Self-assign | Admin assign to self | ✅ Success |
| View filter | Teknisi view machines | ✅ Only assigned |

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 11 Jan 2026 3:27 PM | Initial plan |
| 1.1 | 11 Jan 2026 3:38 PM | +Hierarchical assignment, +Multi-user, +Self-assign |
