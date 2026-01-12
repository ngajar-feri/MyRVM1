# Secure /api/documentation Endpoint - Development Plan

**Versi Dokumen**: 1.1  
**Tanggal Revisi**: Sabtu-11 Januari 2026 - 02:10 PM  
**Tujuan**: Mengamankan endpoint `/api/documentation` (Swagger UI) agar hanya bisa diakses oleh user yang terautentikasi dengan role tertentu.  
**Status**: ✅ Selesai

---

## 1. Ringkasan Perubahan

### Problem
1. Endpoint `/api/documentation` tidak memiliki middleware - bisa diakses siapa saja
2. L5-Swagger routes tidak menggunakan `web` middleware - session tidak tersedia

### Solution
1. Membuat `SwaggerAuthMiddleware` dengan:
   - Auth check via `auth('web')`
   - Role check untuk allowed roles
   - Custom styled 403 page (bukan redirect)
2. Menambahkan `'web'` middleware di config l5-swagger SEBELUM middleware auth

### Allowed Roles
- `super_admin`
- `admin`
- `operator`
- `teknisi`

---

## 2. Files Modified

| File | Change |
|------|--------|
| `app/Http/Middleware/SwaggerAuthMiddleware.php` | [NEW] Middleware with custom 403 page |
| `config/l5-swagger.php` | [MODIFY] Added 'web' + SwaggerAuthMiddleware |

---

## 3. Key Config Change

```php
// config/l5-swagger.php
'middleware' => [
    'api' => [
        'web',  // Enable session for authentication
        \App\Http\Middleware\SwaggerAuthMiddleware::class,
    ],
],
```

---

## 4. Verification Results

| Test | Expected | Result |
|------|----------|--------|
| Iframe in dashboard (admin) | Swagger UI visible | ✅ PASS |
| Direct access (logged admin) | Swagger UI visible | ✅ PASS |
| Direct access (not logged in) | Custom 403 page | ✅ PASS |

---

## 5. User Flow

### ✅ Authorized Flow:
1. User login dengan role (Super Admin/Admin/Teknisi/Operator)
2. Masuk Dashboard → Klik "API Documentation"
3. Iframe menampilkan Swagger UI dengan lengkap

### ❌ Unauthorized Flow:
1. User tanpa login akses langsung `/api/documentation`
2. Muncul Custom 403 Page dengan pesan "Authentication required"
3. Tombol "Go to Login" mengarah ke halaman login

---

## 6. Rollback Plan

1. Kembalikan `l5-swagger.php` middleware ke `'api' => []`
2. Hapus file `SwaggerAuthMiddleware.php`
3. Clear config cache: `php artisan config:clear`

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 11 Jan 2026 1:55 PM | Initial implementation - SwaggerAuthMiddleware |
| 1.1 | 11 Jan 2026 2:10 PM | Fixed session issue - Added 'web' middleware |
| 1.2 | 11 Jan 2026 2:50 PM | Added styled 403 page for /dashboard/api-docs route |


