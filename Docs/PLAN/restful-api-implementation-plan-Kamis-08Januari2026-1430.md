Versi Dokumen: 1.0
Tanggal Revisi: Kamis-08 Januari 2026 - 02:30 PM
Tujuan: Merancang spesifikasi dan langkah implementasi RESTful API lanjutan untuk manajemen User, Tenant, RVM, dan IoT.
Status: Selesai

# Rencana Implementasi RESTful API (Fase 2 & 3)

## 1. Pendahuluan
Dokumen ini menjabarkan spesifikasi teknis untuk pengembangan API modul inti MyRVM. Fokus utama adalah menyediakan endpoint yang dibutuhkan oleh aplikasi mobile (User/Tenant) dan perangkat keras (RVM/Edge).

## 2. Cakupan & Peran (Roles Scope)

| Modul | Role Utama | Deskripsi Singkat |
| :--- | :--- | :--- |
| **User Management** | User | Registrasi, Profil, Ubah Password. |
| **Tenant Management** | Tenant | CRUD Voucher (Milik sendiri). |
| **RVM Management** | Admin/Operator | CRUD Mesin, Monitoring Status. |
| **Edge Telemetry** | Device | Pengiriman data sensor (Berat, Status Penuh). |
| **System Logs** | Admin | Audit trail aktivitas sistem. |

---

## 3. Spesifikasi Endpoint

### A. User Management (Auth & Profile)
*Controller: `Api/AuthController`, `Api/UserController`*

| Method | Endpoint | Deskripsi | Middleware |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/register` | Pendaftaran user baru (Role: User) | Public |
| `GET` | `/api/v1/profile` | Ambil data profil user login | Auth:Sanctum |
| `PUT` | `/api/v1/profile` | Update nama/email profil | Auth:Sanctum |
| `PUT` | `/api/v1/change-password` | Ganti password | Auth:Sanctum |

### B. Tenant Management (Vouchers)
*Controller: `Api/TenantVoucherController`*

| Method | Endpoint | Deskripsi | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/tenant/vouchers` | List voucher milik tenant | Auth:Sanctum, Role:Tenant |
| `POST` | `/api/v1/tenant/vouchers` | Buat voucher baru | Auth:Sanctum, Role:Tenant |
| `PUT` | `/api/v1/tenant/vouchers/{id}` | Update voucher | Auth:Sanctum, Role:Tenant |
| `DELETE` | `/api/v1/tenant/vouchers/{id}` | Hapus voucher | Auth:Sanctum, Role:Tenant |

### C. RVM Machine Management
*Controller: `Api/RvmMachineController`*

| Method | Endpoint | Deskripsi | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/rvm-machines` | List semua mesin RVM | Auth:Sanctum, Role:Admin/Operator |
| `POST` | `/api/v1/rvm-machines` | Tambah mesin baru | Auth:Sanctum, Role:Admin |
| `GET` | `/api/v1/rvm-machines/{id}` | Detail mesin & status | Auth:Sanctum, Role:Admin/Operator |
| `PUT` | `/api/v1/rvm-machines/{id}` | Update konfigurasi mesin | Auth:Sanctum, Role:Admin |

### D. Edge Device Telemetry (IoT)
*Controller: `Api/EdgeDeviceController`*

| Method | Endpoint | Deskripsi | Middleware |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/devices/{id}/telemetry` | Kirim data sensor (berat, kapasitas) | Auth:Sanctum (Device Token) |
| `POST` | `/api/v1/devices/{id}/heartbeat` | Ping status online | Auth:Sanctum (Device Token) |

### E. System Logs
*Controller: `Api/LogController`*

| Method | Endpoint | Deskripsi | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/logs` | Lihat log aktivitas sistem | Auth:Sanctum, Role:Admin |

---

## 4. Rencana Implementasi

### Tahap 1: Persiapan (Database)
1.  Buat Model & Migration untuk `RvmMachine` (jika belum ada/lengkap).
2.  Buat Model & Migration untuk `TelemetryData`.

### Tahap 2: Pengembangan Controller
1.  **User Module**: Refactor `AuthController`, buat `UserController`.
2.  **Tenant Module**: Buat `TenantVoucherController`.
3.  **RVM Module**: Buat `RvmMachineController`.
4.  **IoT Module**: Buat `EdgeDeviceController`.

### Tahap 3: Testing & Dokumentasi
1.  Update anotasi Swagger di setiap controller baru.
2.  Generate ulang dokumentasi.
3.  Test endpoint menggunakan Postman/Curl.

## 5. Catatan Khusus Role Operator
Role `operator` akan dipertahankan untuk kebutuhan monitoring lapangan. Operator memiliki akses `Read-Only` ke data RVM dan Logs, namun tidak memiliki hak `Write/Delete` konfigurasi mesin (hanya Admin).
