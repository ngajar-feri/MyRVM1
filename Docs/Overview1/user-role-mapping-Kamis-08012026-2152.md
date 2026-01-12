# Dokumentasi Teknis: Mapping Kredensial & Role (MyRVM Ecosystem)

**Versi Dokumen:** 1.0
**Tanggal Revisi:** Kamis, 08 Januari 2026 - 09:52 PM
**Tujuan:** Mendefinisikan matriks akses, peran pengguna (human), dan peran sistem (machine) dalam ekosistem MyRVM untuk memastikan keamanan dan isolasi data yang tepat.

## 1. Definisi Entitas & Aktor

Dalam ekosistem MyRVM, terdapat dua jenis aktor yang memerlukan autentikasi dan otorisasi:

1.  **Human Actors (Pengguna Manusia):** Berinteraksi melalui Dashboard Web atau Aplikasi Mobile.
2.  **System Actors (Mesin/Perangkat):** Berinteraksi melalui API Token (Sanctum) atau Client Credentials.

## 2. Matriks Peran (Roles)

### A. Human Roles (Tabel `roles`)

| Role | Kode Slug | Deskripsi & Cakupan Akses |
| :--- | :--- | :--- |
| **Super Admin** | `super-admin` | **Akses Penuh (Global).** Mengelola Tenant, Admin, Konfigurasi Sistem Global, Manajemen API Key Mesin, dan Monitoring RVM-CV. |
| **Admin** | `admin` | **Akses Terbatas (Tenant Level).** Mengelola RVM di wilayah/tenant-nya, Manajemen User (User Support), dan Laporan Transaksi Tenant. |
| **Tenant** | `tenant` | **Akses Mitra.** Mengelola stok Voucher, Validasi penukaran poin user, dan melihat laporan klaim voucher. |
| **User** | `user` | **Pengguna Akhir.** Melakukan transaksi deposit botol, melihat saldo poin, dan menukarkan poin dengan voucher via Aplikasi Mobile. |

### B. System Roles (Token Abilities / Scopes)

Mesin tidak login menggunakan email/password, melainkan menggunakan API Key atau Token yang di-generate dengan *capabilities* tertentu.

| System Actor | Tipe Kredensial | Scope / Capabilities |
| :--- | :--- | :--- |
| **RVM-Edge** | API Key (Per Device) | `rvm:init-session` (Meminta QR Sesi), `rvm:submit-transaction` (Kirim hasil deteksi), `rvm:log-status` (Kirim heartbeat/sensor data). |
| **RVM-CV** | Service Token | `cv:fetch-dataset` (Ambil gambar fraud), `cv:upload-model` (Update best.pt), `cv:read-job` (Cek antrian training). |

## 3. Mapping Kredensial & Autentikasi

### 3.1. Alur Autentikasi Manusia
*   **Metode:** OAuth2 (Google, Line, Discord) & Email/Password.
*   **Penyimpanan:** Tabel `users` dengan kolom `role_id`.
*   **Verifikasi:**
    *   Login Email: Wajib Verifikasi Email (`MustVerifyEmail`).
    *   Login Sosmed: Auto-verified (mengikuti status akun sosmed).

### 3.2. Alur Autentikasi RVM-Edge (Mesin)
*   **Metode:** `X-RVM-API-KEY` Header.
*   **Mapping:** Setiap unit fisik Jetson Orin Nano memiliki satu record di tabel `reverse_vending_machines`.
*   **Kredensial:**
    *   `device_id`: UUID unik mesin.
    *   `api_key`: String acak 64-karakter (disimpan terenkripsi/hashed di DB).
*   **Mekanisme:** Saat booting, RVM-Edge melakukan *handshake* ke RVM-Server. Jika valid, Server memberikan Temporary Access Token untuk sesi WebSocket.

### 3.3. Alur Autentikasi RVM-CV (Training Server)
*   **Metode:** Bearer Token (Sanctum).
*   **Mapping:** Dianggap sebagai `Special User` atau `Service Account` di backend.
*   **Mekanisme:** VM102 menggunakan token statis (yang dirotasi berkala) untuk berkomunikasi dengan endpoint khusus CV di RVM-Server.

## 4. Permission Matrix (Detail Izin)

| Fitur / Modul | Super Admin | Admin | Tenant | User | RVM-Edge | RVM-CV |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **User Mgmt** | Full | View/Edit (Scoped) | - | Self (Profile) | - | - |
| **Tenant Mgmt** | Full | - | Self (Profile) | - | - | - |
| **RVM Mgmt** | Full (Create/Delete) | Monitor/Reboot | - | - | Self (Status) | - |
| **Transactions** | View All | View (Scoped) | View (Redeem only) | View (Own) | Create | - |
| **Vouchers** | Manage Categories | - | Manage Own Stock | Redeem | - | - |
| **AI Models** | Manage/Deploy | View Version | - | - | Download | Upload |
| **Logs** | Full Audit | System Logs | - | - | Upload Logs | Upload Logs |

## 5. Implementasi di Laravel 12

*   **Middleware:** `auth:sanctum` untuk API, `role:slug` untuk pembatasan akses controller.
*   **Policies:** Menggunakan Laravel Policies untuk logika otorisasi granular (contoh: `UserPolicy`, `RvmPolicy`).
*   **Database Seeder:** `RolePermissionSeeder` harus dijalankan saat deployment awal untuk mengisi tabel `roles` dan `permissions` sesuai matriks di atas.