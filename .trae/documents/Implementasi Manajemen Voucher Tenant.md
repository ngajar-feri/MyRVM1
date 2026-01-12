# Rencana Implementasi Fitur Manajemen Voucher untuk Tenant

Terima kasih atas konfirmasinya. Saya telah memverifikasi bahwa file dokumen `user-role-mapping-Kamis-08012026-1100.md` **memang ada** di folder `Docs`. Kemungkinan sebelumnya terlewat karena tidak di-refresh di file explorer Anda.

Untuk fitur **Manajemen Voucher oleh Tenant**, berikut adalah rencana implementasi komprehensifnya:

## 1. Desain Database & Model
*   **Tabel Baru**: `vouchers`
    *   `id`: Primary Key
    *   `tenant_id`: Foreign Key ke `users` (hanya role 'tenan')
    *   `title`: Nama promosi
    *   `description`: Detail promo
    *   `image`: URL gambar promo
    *   `points_required`: Jumlah poin untuk redeem
    *   `valid_until`: Tanggal kadaluarsa
    *   `status`: 'active', 'inactive'
*   **Relasi**:
    *   User (Tenant) `hasMany` Vouchers
    *   Voucher `belongsTo` User (Tenant)

## 2. Dashboard Khusus Tenant (`dashboard.tenant`)
Saya akan membuat view khusus untuk role `tenan` yang berisi:
*   **Ringkasan**: Jumlah voucher aktif, total redeem (mockup).
*   **Manajemen Voucher**: Tabel daftar voucher dengan tombol **Tambah Voucher Baru**, Edit, dan Hapus.
*   **Form Input**: Modal atau halaman untuk input detail voucher.

## 3. Kontroller & Logika Bisnis
*   **`VoucherController`**: Menangani operasi CRUD (Create, Read, Update, Delete).
    *   Memastikan Tenant hanya bisa mengedit/hapus voucher miliknya sendiri (Policy).
*   **API Endpoint**: Menambahkan endpoint JSON (misal: `/api/vouchers`) agar aplikasi Mobile User bisa mengambil daftar voucher aktif.

## 4. Langkah Eksekusi
1.  Buat Migration & Model `Voucher`.
2.  Buat `VoucherController` dan route terkait.
3.  Buat View `dashboard.tenant` yang mengintegrasikan fitur manajemen voucher.
4.  Update `DashboardController` agar role `tenan` diarahkan ke dashboard baru ini.
5.  Update dokumen `Docs/user-role-mapping...` dengan kapabilitas baru ini.

Apakah Anda setuju dengan spesifikasi tabel dan fitur ini?
