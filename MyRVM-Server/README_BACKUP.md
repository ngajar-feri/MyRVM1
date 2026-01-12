# Backup Project: MyRVM-Server
Timestamp: 10012026-1437
Code: [TR1]

## Deskripsi
Backup ini berisi source code lengkap dari aplikasi backend **MyRVM-Server**.
Project ini dibangun menggunakan:
- Laravel 10
- MySQL
- L5-Swagger (API Documentation)
- Laravel Sanctum (Authentication)

## Struktur Direktori Utama
- `app/`: Logika aplikasi (Controllers, Models, Middleware).
- `database/`: Migrations dan Seeders.
- `routes/`: Definisi endpoint API (`api.php`) dan Web (`web.php`).
- `config/`: Konfigurasi sistem (termasuk `l5-swagger.php`).
- `storage/api-docs/`: File JSON dokumentasi Swagger.

## Catatan Penting
- Dokumentasi API telah diperbarui dengan kode status lengkap (200, 400, 401, 500, dll).
- Swagger UI telah dikonfigurasi dengan `responseInterceptor` untuk auto-fill token.
- Folder ini siap untuk di-deploy atau di-restore.
