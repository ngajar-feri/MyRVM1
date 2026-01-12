# Rencana Audit & Implementasi Role-Based UI/UX

Berdasarkan permintaan Anda untuk audit keamanan yang mencakup UI/UX spesifik per role, berikut adalah rencana implementasi komprehensif:

## 1. Audit & Dokumentasi (Sesuai Poin 1 & 4)
Saya akan membuat dokumen `Docs/user-role-mapping-[Hari]-[Tanggal].md` yang mencakup:
*   **Mapping Kredensial**: Daftar user default (`superadmin`, `admin`, `operator`, `tenan`, `user`) dan role-nya.
*   **Analisis Gap**: Identifikasi bahwa saat ini semua role melihat dashboard yang sama, dan belum ada middleware yang membatasi akses.
*   **Permission Matrix**: Usulan matriks akses untuk perbaikan (siapa boleh akses apa).

## 2. Diferensiasi UI/UX Berbasis Role (Sesuai Poin 1)
Agar setiap kredensial memiliki pengalaman berbeda, saya akan memodifikasi `DashboardController`:
*   **Super Admin / Admin**: Akses ke Dashboard Analitik Penuh (View saat ini).
*   **Operator**: Akses ke Dashboard Operasional (Fokus pada status mesin & alert).
*   **User / Tenant**: Akses ke Dashboard Personal (Riwayat transaksi & poin).
*   **Implementasi**: Controller akan mengecek `auth()->user()->role` dan me-render view yang berbeda (`dashboard.admin`, `dashboard.operator`, `dashboard.user`).

## 3. Penguatan Keamanan & RBAC (Sesuai Poin 2 & 3)
*   **Middleware**: Membuat `EnsureUserHasRole` untuk memproteksi route. Contoh: `/admin/*` hanya bisa diakses role admin.
*   **Logging**: Membuat `UserObserver` untuk mencatat setiap perubahan pada data user (password/role) ke log file dengan timestamp WIB+7.

## 4. Validasi & Pengujian (Sesuai Poin 5)
*   **Test Script**: Membuat script otomatis untuk memverifikasi:
    *   Login user dengan berbagai role.
    *   Pengecekan akses (apakah User biasa ditolak saat akses halaman Admin).
    *   Pengecekan konten (apakah User melihat Dashboard Personal, bukan Admin).

Apakah Anda menyetujui rencana eksekusi ini?
