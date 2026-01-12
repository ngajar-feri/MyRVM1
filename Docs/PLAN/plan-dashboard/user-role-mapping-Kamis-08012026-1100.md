# Audit Mapping Kredensial dan Role Pengguna
**Tanggal:** Kamis, 08 Januari 2026
**Waktu:** 11:00 WIB
**Project:** MyRVM-Server

## 1. Identitas & Kredensial Pengguna Default
Berdasarkan pemeriksaan pada `DatabaseSeeder.php`, berikut adalah daftar pengguna default yang terdaftar dalam sistem beserta role masing-masing:

| Username/Email | Password Default | Role (Database) | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| `superadmin@myrvm.com` | `password` | `super_admin` | Akses penuh ke seluruh modul sistem dan konfigurasi. |
| `admin@myrvm.com` | `password` | `admin` | Manajemen operasional harian, user, dan laporan. |
| `operator@myrvm.com` | `password` | `operator` | Monitoring status mesin RVM dan penanganan alert. |
| `tenan@myrvm.com` | `password` | `tenan` | Manajemen lokasi spesifik dan laporan penjualan tenant. |
| `user@myrvm.com` | `password` | `user` | Pengguna aplikasi end-user (poin, riwayat transaksi). |

## 2. Analisis Implementasi Saat Ini
### a. Struktur Database
- Tabel `users` memiliki kolom `role` (string) sebagai penanda hak akses.
- Tidak ditemukan tabel relasi kompleks (`model_has_roles`, `permissions`) atau penggunaan library pihak ketiga seperti `spatie/laravel-permission`.

### b. Temuan Celah Keamanan (Gap Analysis)
1.  **Missing Authorization Middleware**:
    - Saat ini, rute `/dashboard` hanya dilindungi oleh middleware `auth`.
    - Tidak ada pengecekan role spesifik. Artinya, `user` biasa dapat mengakses halaman yang sama dengan `admin`.
2.  **Shared UI/UX**:
    - Semua role diarahkan ke `DashboardController@index` yang me-render view `dashboard` yang sama.
    - Tidak ada diferensiasi tampilan berdasarkan kebutuhan role (Prinsip *Least Privilege* pada UI belum diterapkan).

## 3. Matriks Hak Akses (Usulan Perbaikan)

| Fitur / Modul | Super Admin | Admin | Operator | Tenan | User |
| :--- | :---: | :---: | :---: | :---: | :---: |
| Dashboard Analitik (Global) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Dashboard Operasional (Mesin) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Dashboard Tenant (Vouchers) | ✅ | ❌ | ❌ | ✅ | ❌ |
| Dashboard Personal (Poin) | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manajemen User | ✅ | ✅ | ❌ | ❌ | ❌ |
| Konfigurasi Sistem | ✅ | ❌ | ❌ | ❌ | ❌ |

## 4. Pembaruan Fitur Tenant (08/01/2026 14:30 WIB)
Role `tenan` kini memiliki kapabilitas khusus untuk mengelola Voucher & Promosi:
- **Hak Akses**: Create, Read, Update, Delete (CRUD) Voucher.
- **Batasan**: Tenant hanya bisa mengelola voucher miliknya sendiri.
- **Integrasi**: Voucher yang berstatus 'active' akan terekspos melalui API (`/api/vouchers`) untuk ditampilkan di Aplikasi Mobile User.

## 5. Log Perubahan Audit
Dokumen ini dibuat sebagai baseline audit. Setiap perubahan pada mapping role di atas harus dicatat dalam log sistem.

**Rekomendasi Tindakan (Status: Completed):**
1. Implementasi `EnsureUserHasRole` middleware (Selesai).
2. Pemisahan View Dashboard berdasarkan Role (Selesai).
3. Aktivasi Logging Audit untuk model User (Selesai).
4. Implementasi Modul Voucher untuk Tenant (Selesai).
