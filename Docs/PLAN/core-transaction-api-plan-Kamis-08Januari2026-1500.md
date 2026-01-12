Versi Dokumen: 1.0
Tanggal Revisi: Kamis-08 Januari 2026 - 03:00 PM
Tujuan: Mengimplementasikan API Bisnis Inti (Core Business Logic) untuk Transaksi, Penukaran Poin, dan Operasional Teknisi.
Status: Selesai

# Rencana Implementasi API Core Transaction & Operation

## 1. Pendahuluan
Fase ini berfokus pada logika bisnis utama aplikasi: bagaimana user mendapatkan poin dari mesin (RVM), bagaimana user menggunakan poin (Redemption), dan bagaimana teknisi merawat mesin.

## 2. Cakupan Fitur

### A. RVM Transaction (Siklus Deposit Sampah)
*   **User Flow**: Scan QR -> Masukkan Botol -> Selesai -> Dapat Poin.
*   **API Needs**:
    1.  `POST /api/v1/transactions/start`: Memulai sesi transaksi (validasi user & lokasi).
    2.  `POST /api/v1/transactions/item`: Mengirim data item yang dideteksi (validasi AI).
    3.  `POST /api/v1/transactions/commit`: Menyelesaikan sesi dan mengkreditkan poin.

### B. Redemption (Penukaran Voucher)
*   **User Flow**: Pilih Voucher -> Tukar Poin -> Dapat Kode Voucher -> Gunakan di Tenant.
*   **API Needs**:
    1.  `POST /api/v1/redemption/redeem`: Menukar poin dengan voucher.
    2.  `POST /api/v1/redemption/validate`: Tenant memvalidasi/menggunakan voucher user.

### C. Technician & Maintenance
*   **Flow**: Admin assign tugas -> Teknisi dapat PIN -> Teknisi login di Mesin -> Maintenance -> Log.
*   **API Needs**:
    1.  `GET /api/v1/technician/assignments`: List tugas perbaikan.
    2.  `POST /api/v1/technician/generate-pin`: Request PIN sementara untuk akses mesin.
    3.  `POST /api/v1/technician/validate-pin`: (Dipanggil oleh Mesin) Validasi PIN.

## 3. Rencana Teknis
1.  **Database**:
    *   Buat tabel `transactions` dan `transaction_items`.
    *   Buat tabel `user_vouchers` (pivot table untuk kepemilikan voucher).
    *   Buat tabel `maintenance_logs` dan `technician_assignments`.
2.  **Controller**:
    *   `Api/TransactionController`
    *   `Api/RedemptionController`
    *   `Api/TechnicianController`

## 4. Langkah Eksekusi
1.  Buat Model & Migration yang diperlukan.
2.  Implementasi Controller Transaksi.
3.  Implementasi Controller Redemption.
4.  Implementasi Controller Teknisi.
5.  Update Dokumentasi Swagger.
