# Rencana Implementasi RESTful API Lanjutan

## 1. Pembaruan Status Dokumentasi
Sebelum memulai pengembangan fitur baru, kita akan menandai dokumen rencana yang telah diselesaikan agar status proyek jelas.
*   `backend-foundation-plan`: **Status: Selesai**
*   `api-testing-plan`: **Status: Selesai**
*   `api-integration-plan`: **Status: Selesai**

## 2. Rencana Pengembangan API (Fase 2 & 3)
Kita akan membuat dokumen rencana baru: `restful-api-implementation-plan-Kamis-08Januari2026-1430.md`.

### Lingkup Pengembangan:
#### A. User Management API (Role: User)
*   **Endpoint**: `/api/v1/register`, `/api/v1/profile`, `/api/v1/change-password`.
*   **Controller**: Update `AuthController` atau buat `UserController` baru.
*   **Fungsi**: Memungkinkan registrasi pengguna baru dari aplikasi mobile dan manajemen profil mandiri.

#### B. Tenant Management API (Role: Tenant)
*   **Endpoint**: `/api/v1/tenant/vouchers` (CRUD).
*   **Akses**: Khusus User dengan role `tenan`.
*   **Fungsi**: Tenant dapat mengelola voucher mereka sendiri via API (Create, Update, Delete). *Saat ini sudah ada endpoint Public GET, perlu endpoint management untuk Tenant App.*

#### C. RVM Machine Management API (Role: Admin/Operator)
*   **Endpoint**: `/api/v1/rvm-machines` (CRUD).
*   **Controller**: `Api/RvmMachineController`.
*   **Model**: `RvmMachine` (perlu dibuat jika belum ada).
*   **Fungsi**: Admin dapat mengelola inventaris mesin via API; Aplikasi monitoring bisa mengambil daftar mesin.

#### D. Edge Device & Telemetry API (Role: Machine/Device)
*   **Endpoint**: `/api/v1/devices/{id}/telemetry` (POST).
*   **Controller**: `Api/EdgeDeviceController`.
*   **Fungsi**: Menerima data sensor (berat sampah, status penuh) dari perangkat IoT di lapangan.

#### E. System Logs API (Role: Admin)
*   **Endpoint**: `/api/v1/logs` (GET).
*   **Controller**: `Api/LogController`.
*   **Fungsi**: Menyediakan data log aktivitas sistem untuk dashboard monitoring eksternal.

## 3. Klarifikasi Role Operator
Berdasarkan temuan inkonsistensi antara kode (ada `operator`) dan dokumen terbaru (diganti `technician`/admin), **rencana ini akan tetap mendukung role `operator`** untuk menjaga kompatibilitas dengan kode yang ada saat ini, namun dengan catatan bahwa role ini fokus pada **Monitoring & Maintenance**.

## 4. Langkah Eksekusi
1.  **Update Docs**: Menambahkan status pada header dokumen lama.
2.  **Create Plan**: Menulis dokumen rencana baru secara detail.
3.  **Implementasi**: Mulai coding dari User Management -> Tenant Management -> RVM -> Device -> Logs.
4.  **Testing**: Verifikasi setiap endpoint baru dengan Swagger/Curl.

Apakah Anda setuju untuk memulai fase pengembangan API lanjutan ini dengan cakupan di atas?
