Versi Dokumen: 1.0
Tanggal Revisi: Kamis-09 Januari 2026 - 03:30 PM
Tujuan: Dokumentasi implementasi fitur Core Transaction, Redemption, dan Technician Module serta strategi testing dan rollback.
Status: Selesai

# Laporan Implementasi Core Transaction & Operations

## 1. Ringkasan Implementasi
Kami telah menyelesaikan pengembangan backend untuk logika bisnis inti aplikasi MyRVM, meliputi:
1.  **Siklus Transaksi RVM**: Start Session -> Deposit Item -> Commit Transaction.
2.  **Manajemen Poin & Voucher**: User mendapatkan poin, menukarkannya dengan voucher, dan tenant memvalidasi voucher.
3.  **Modul Teknisi**: Penugasan, Keamanan Akses (PIN), dan Log Maintenance.

## 2. Detail Perubahan Teknis

### A. Database Schema
*   **Users**: Penambahan `points_balance` (BigInt) dan `gold_balance` (Decimal).
*   **Transactions**: Tabel baru `transactions` (Header) dan `transaction_items` (Detail).
*   **Vouchers**: Tabel baru `user_vouchers` dengan kolom `unique_code` dan `status`.
*   **Technician**: Tabel `technician_assignments` dan `maintenance_logs`.

### B. API Endpoints (Baru)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/api/v1/transactions/start` | Memulai sesi transaksi di mesin RVM. |
| `POST` | `/api/v1/transactions/item` | Mencatat item yang dimasukkan ke RVM. |
| `POST` | `/api/v1/transactions/commit` | Menyelesaikan transaksi dan menambah poin. |
| `POST` | `/api/v1/redemption/redeem` | Menukar poin user dengan voucher. |
| `POST` | `/api/v1/redemption/validate` | Tenant memvalidasi penggunaan voucher. |
| `GET` | `/api/v1/technician/assignments` | List tugas maintenance teknisi. |
| `POST` | `/api/v1/technician/generate-pin` | Generate PIN akses mesin sementara. |
| `POST` | `/api/v1/technician/validate-pin` | Validasi PIN akses di sisi mesin. |

## 3. Rencana Pengujian (Staging Plan)

Sebelum deploy ke production, fitur ini harus diuji di environment Staging (Local Docker) dengan skenario berikut:

### Skenario 1: Full Transaction Cycle
1.  Login sebagai User (`/api/v1/login`).
2.  Start Transaction (`/api/v1/transactions/start`) dengan `rvm_id` valid.
3.  Deposit 2 Item (`/api/v1/transactions/item`).
4.  Commit Transaction (`/api/v1/transactions/commit`).
5.  **Verifikasi**: Cek `points_balance` user bertambah, status transaksi `completed`.

### Skenario 2: Redemption Flow
1.  Pastikan user punya poin cukup.
2.  Redeem Voucher (`/api/v1/redemption/redeem`).
3.  Login sebagai Tenant (`/api/v1/login`).
4.  Validate Voucher (`/api/v1/redemption/validate`) menggunakan kode dari step 2.
5.  **Verifikasi**: Voucher status `used`, poin user berkurang.

### Skenario 3: Technician Access
1.  Login sebagai Teknisi.
2.  Generate PIN untuk assignment aktif.
3.  Panggil `/validate-pin` (sebagai Mesin) dengan PIN tersebut.
4.  **Verifikasi**: Respons sukses "Access Granted".

## 4. Rollback Plan

Jika terjadi kegagalan kritis setelah deployment, ikuti langkah berikut:

### Level 1: API Error (Logic Bug)
*   Revert commit kode terakhir via Git.
*   Deploy ulang versi stabil sebelumnya.

### Level 2: Database Corruption / Schema Issue
*   **Warning**: Rollback migrasi database akan menghapus data transaksi baru. Lakukan backup database terlebih dahulu.
*   Command Rollback:
    ```bash
    php artisan migrate:rollback --step=5
    ```
    (Langkah ini akan membatalkan 5 migrasi terakhir: transaction re-create, technician tables, user voucher, points column).
*   Restore data dari backup snapshot sebelum deploy jika diperlukan.

## 5. Catatan Tambahan
*   Dokumentasi Swagger (`api-docs.json`) saat ini dikelola secara semi-manual karena isu pada library generator. Pastikan update manual jika menambah endpoint baru.
