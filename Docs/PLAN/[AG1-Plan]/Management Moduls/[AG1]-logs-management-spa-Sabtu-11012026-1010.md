# Logs Management SPA Module - Development Plan

**Versi Dokumen**: 1.1  
**Tanggal Revisi**: Sabtu-11 Januari 2026 - 10:35 AM  
**Tujuan**: Membuat halaman SPA untuk menu Logs yang menampilkan data dari tabel activity_logs dengan role-based access control.  
**Status**: ✅ Selesai

---

## 1. Ringkasan Perubahan

### Fitur yang akan ditambahkan:
1. **Logs SPA Page** - Halaman untuk melihat activity logs
2. **Role-Based API Security** - Restrict `/api/v1/logs` to allowed roles
3. **ActivityLog Model** - Eloquent model untuk tabel activity_logs
4. **Data Seeder** - Sample data untuk testing

### Roles yang diizinkan:
- `super_admin`
- `admin`  
- `operator`
- `teknisi`

### Roles yang TIDAK diizinkan:
- `user`
- `tenan`

---

## 2. Current State Analysis

### Issue Ditemukan

> ⚠️ **BUG SECURITY**: API endpoint `/api/v1/logs` saat ini **MASIH BISA DIAKSES OLEH SEMUA ROLE** termasuk Tenant!

### Evidence:
```php
// routes/api.php - Line 121
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // ...
    Route::get('/logs', [LogController::class, 'index']); // NO ROLE CHECK!
});
```

### Solution:
Tambahkan role middleware:
```php
Route::get('/logs', [LogController::class, 'index'])
    ->middleware('role:super_admin,admin,operator,teknisi');
```

---

## 3. Files to Create/Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Models/ActivityLog.php` | NEW | Eloquent model |
| `app/Http/Controllers/Api/LogController.php` | MODIFY | Query dari DB, add role check |
| `routes/api.php` | MODIFY | Add role middleware |
| `app/Http/Controllers/Dashboard/LogsController.php` | NEW | Dashboard controller |
| `routes/web.php` | MODIFY | Add logs route |
| `resources/views/dashboard/logs/index.blade.php` | NEW | Full page view |
| `resources/views/dashboard/logs/index-content.blade.php` | NEW | SPA content |
| `public/js/modules/logs.js` | NEW | SPA module |
| `public/js/spa-navigator.js` | MODIFY | Add logs to module map |
| `resources/views/layouts/app.blade.php` | MODIFY | Update menu href |

---

## 4. Database Schema

**Table: activity_logs**
```sql
CREATE TABLE activity_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    module VARCHAR(255),      -- Auth, Device, Machine, System
    action VARCHAR(255),      -- Login, Update, Error, Warning
    description TEXT,
    ip_address VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 5. API Specification

### GET /api/v1/logs

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| page | int | Pagination page |
| per_page | int | Items per page (default: 20) |
| module | string | Filter by module |
| action | string | Filter by action |
| date_from | date | Filter from date |
| date_to | date | Filter to date |

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user": { "id": 1, "name": "Admin" },
      "module": "Auth",
      "action": "Login",
      "description": "User logged in successfully",
      "ip_address": "127.0.0.1",
      "created_at": "2026-01-11T10:00:00Z"
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "total": 100
}
```

---

## 6. Verification Plan

### Test 1: API Role Restriction
1. Login as Tenant
2. Call `/api/v1/logs`
3. **Expected**: 403 Forbidden

### Test 2: API Access for Admin
1. Login as Admin
2. Call `/api/v1/logs`
3. **Expected**: 200 OK with data

### Test 3: SPA Navigation
1. Login as Admin
2. Click Logs menu from Dashboard
3. **Expected**: Data loads via SPA

---

## 7. Rollback Plan

Jika terjadi masalah:
1. Revert `LogController` ke versi sebelumnya (file-based)
2. Hapus role middleware dari route
3. Menu tetap existing (sudah berfungsi)

---

## Changelog

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 11 Jan 2026 10:10 AM | Antigravity AI | Initial development plan |
