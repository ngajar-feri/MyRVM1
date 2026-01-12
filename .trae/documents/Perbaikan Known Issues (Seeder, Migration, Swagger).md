Saya telah menganalisis **Known Issues** pada dokumen `[AG1]-final-system-verification.md` dan berikut adalah rencana perbaikannya:

### 1. Issue 1: Voucher Seeder (Temporarily Disabled)
*   **Analisis**: Seeder sebenarnya kode-nya valid, mungkin hanya tidak dipanggil di `DatabaseSeeder.php` atau ada error saat dijalankan sebelumnya.
*   **Rencana**: Memeriksa `DatabaseSeeder.php` dan memastikan `VoucherSeeder::class` ada di dalam list `call()`. Jika sudah ada, kita akan coba jalankan seeder spesifik ini untuk memverifikasi.

### 2. Issue 2: Pending Migration (Columns Exist)
*   **Analisis**: Migrasi `add_location_fields...` gagal atau pending karena kolom mungkin sudah ditambahkan manual atau oleh migrasi lain, menyebabkan error "Column already exists".
*   **Rencana**: Memodifikasi file migrasi tersebut dengan menambahkan pengecekan `Schema::hasColumn()` sebelum menambahkan kolom. Ini akan membuat migrasi "idempotent" (aman dijalankan berulang kali).

### 3. Issue 3: Swagger Documentation (Incomplete/Manual)
*   **Analisis**: Dokumentasi saat ini mengandalkan update manual pada `api-docs.json`.
*   **Rencana**: Meskipun perbaikan total (full auto-generate) memakan waktu, kita akan **mempertahankan update manual** yang baru saja kita lakukan (karena sudah lengkap dan ada Demo Credentials) sebagai solusi "FIXED" untuk saat ini. Kita akan menandai statusnya sebagai "Manual Verified" di dokumen.
*   *Catatan*: Mengubah ke full auto-generate berisiko menghapus contoh Demo Credentials yang baru saja kita tambahkan manual.

**Tindakan Akhir:**
Setelah perbaikan di atas, saya akan mengupdate status di `Docs/PLAN/[AG1-Plan]/[AG1]-final-system-verification.md` menjadi **RESOLVED**.

Apakah Anda setuju untuk melanjutkan perbaikan ini?