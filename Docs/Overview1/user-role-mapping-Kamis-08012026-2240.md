# Dokumentasi Teknis: Mapping Kredensial, Role, & Arsitektur Sistem (MyRVM v1.0)

**Versi Dokumen:** 1.1 [(Revisi dari 1.0)](user-role-mapping-Kamis-08012026-2152.md)
**Tanggal Revisi:** Kamis, 08 Januari 2026 - 10:40 PM
**Tujuan:** Mendefinisikan ulang hak akses pengguna (human) dan sistem (machine) serta alur kerja distribusi model AI dan manajemen sensor sesuai dengan arsitektur MyRVM v1.0.

## 1. Definisi Aktor & Hierarki Peran

Sistem menggunakan pendekatan *Role-Based Access Control* (RBAC) yang hierarkis untuk pengguna manusia.

### A. Human Roles (Tabel `roles` & `users`)

| Role | Kode Slug | Pewarisan | Deskripsi & Kapabilitas |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `super-admin` | - | **God Mode.** Akses penuh ke seluruh konfigurasi sistem, manajemen API Key mesin, manajemen Tenant global, dan akses ke "AI Playground" (RVM-CV). |
| **Admin** | `admin` | - | **Support & Maintenance.** Mengelola User, memantau status RVM-Edge (online/offline/error), dan menangani tiket support. |
| **Tenant** | `tenant` | `user` | **Mitra Bisnis.** Memiliki semua kemampuan **User**, ditambah akses ke Dashboard Tenant untuk membuat/mengelola Voucher dan validasi penukaran poin user. |
| **User** | `user` | - | **Pengguna Akhir.** Login, scan QR di RVM untuk mulai sesi, deposit botol, cek saldo (Poin/Gold), dan tukar poin dengan Voucher. |

### B. System Roles (Machine Identity)

| Aktor Mesin | Autentikasi | Deskripsi Fungsi |
| :--- | :--- | :--- |
| **RVM-Edge** | API Key (Header) | Unit fisik RVM. Memiliki hak untuk memulai sesi transaksi, mengirim telemetri sensor, dan sinkronisasi model AI. |
| **RVM-CV** | Bearer Token | Node komputasi GPU. Memiliki hak untuk membaca dataset gambar (fraud/training) dan mengunggah model `best.pt` baru. |

## 2. Alur Kerja & Interaksi Sistem

### 2.1. Dinamika Sensor RVM-Edge (Flexible Payload)
API RVM-Server harus menerima data sensor secara dinamis (JSON) untuk mengakomodasi penambahan sensor di masa depan tanpa mengubah struktur database secara masif (menggunakan kolom tipe JSONB di PostgreSQL).

*   **Endpoint:** `POST /api/v1/edge/telemetry`
*   **Payload Contoh:**
    ```json
    {
      "device_id": "rvm-jakarta-001",
      "timestamp": "2026-01-08T22:30:00Z",
      "sensors": {
        "ultrasonic_level": 85,          // % kepenuhan bin
        "temperature_internal": 42.5,    // Derajat celcius
        "door_status": "locked",
        "new_sensor_xyz": 123            // Sensor baru bisa langsung ditambahkan
      }
    }
    ```

### 2.2. Manajemen Model AI (Training & Distribusi)

**A. RVM-CV (The Trainer - Stateless)**
1.  **Trigger:** Admin mengupload gambar baru ke "AI Playground" di Server atau memulai jadwal training.
2.  **Process:** RVM-CV mengunduh dataset dan model lama dari Server.
3.  **Execution:** Melakukan Training atau Testing (Inferensi).
4.  **Result:** Mengirim hasil (JSON/Image Result) atau Model Baru (`best.pt`) ke Server.
5.  **Cleanup:** Menghapus seluruh data lokal setelah task selesai.

**B. RVM-Server (The Hub)**
1.  **Storage:** Menyimpan versi-versi `best.pt` di MinIO/S3.
2.  **Versioning:** Mencatat hash (MD5/SHA256) dari setiap model untuk pengecekan update.
3.  **Playground UI:** Menyediakan antarmuka bagi Admin untuk memilih metode pengujian (menggunakan *Current Best Model* atau *Experimental Model* di RVM-CV).

**C. RVM-Edge (The Executor - Semi-Persistent)**
1.  **Routine Check:** Cronjob lokal berjalan (misal: tiap jam 00:00) membandingkan hash model lokal dengan API Server.
2.  **Update:** Jika hash berbeda, RVM-Edge mengunduh `best.pt` baru dari Server (atau GitHub Release sebagai fallback).
3.  **Manual Trigger:** Teknisi dapat menekan tombol "Update AI Model" pada menu *Advanced Interface* di layar sentuh RVM.

### 2.3. User Interface RVM-Edge (Touchscreen)

Layar RVM memiliki dua mode tampilan:

1.  **Public Mode (Default):**
    *   Menampilkan QR Code Sesi (Dinamis).
    *   Instruksi cara pakai.
    *   Saat sesi aktif: Menampilkan Nama User, Jumlah Botol, Total Poin.
2.  **Maintenance Mode (Secured):**
    *   Diakses via Login Khusus (Scan QR Admin / PIN).
    *   Fitur: Kalibrasi Sensor, Test Motor/Conveyor, Cek Koneksi, **Update AI Model**, Lihat Log Lokal.

## 3. Implementasi Keamanan & Token

*   **Stable Coin Foundation:** Struktur database `wallets` harus siap mendukung pencatatan saldo desimal presisi tinggi (untuk konversi gramasi Emas/Crypto) selain poin integer biasa.
*   **API Security:** Komunikasi antara RVM-Edge dan Server wajib menggunakan HTTPS (SSL Pinned jika memungkinkan) untuk mencegah manipulasi data sensor atau poin.