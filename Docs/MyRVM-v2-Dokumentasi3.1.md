Tentu saja. Mari kita buat dokumentasi formal untuk **Fase 1** berdasarkan semua yang telah kita capai dan diskusikan. Ini akan menjadi catatan penting untuk proyek Anda.

---

# **Dokumentasi Pengembangan: MyRVM v1.1**

## **Fase 1: Fondasi & Arsitektur Inti**

**Status Fase:** SELESAI
**Progres Proyek (Setelah Fase 1):** 20%
**Tanggal Penyelesaian Fase:** 22 Mei 2025

### **1. Tujuan Fase**

Tujuan utama dari fase ini adalah untuk membangun dan memvalidasi seluruh fondasi teknis untuk backend `MyRVM-Platform`. Ini mencakup inisialisasi proyek, penyiapan lingkungan pengembangan berbasis Docker yang terisolasi dan portabel, serta pembuatan skema database baru yang akan mendukung semua fitur yang direncanakan.

### **2. Komponen & Teknologi yang Digunakan**

| Komponen                    | Teknologi / Versi Digunakan                | Tujuan                                                                     |
| :-------------------------- | :----------------------------------------- | :------------------------------------------------------------------------- |
| **Backend Framework**       | Laravel 12.x                               | Menyediakan struktur aplikasi, routing, ORM, dan API.                      |
| **Bahasa Pemrograman**      | PHP 8.3                                    | Menjalankan logika backend, dipilih untuk fitur modern dan kompatibilitas. |
| **Database**                | PostgreSQL (Image `postgres:latest`, v17+) | Menyimpan semua data aplikasi secara persisten.                            |
| **Web Server**              | Nginx (Image `nginx:alpine`)               | Melayani request HTTP dan meneruskan request PHP ke PHP-FPM.               |
| **Penyimpanan Objek**       | MinIO (Image `minio/minio:latest`)         | Menyediakan penyimpanan file S3-compatible untuk aset seperti avatar.      |
| **Containerization**        | Docker & Docker Compose                    | Mengemas, mengisolasi, dan mengorkestrasi semua layanan backend.           |
| **Manajemen Repositori**    | Git & GitHub                               | Mengelola dan melacak perubahan kode sumber.                               |
| **Manajemen Konfigurasi**   | `.env` & `git-crypt` (direncanakan)        | Mengelola variabel lingkungan dan rahasia secara aman.                     |
| **Manajemen Versi Node.js** | NVM (Node Version Manager)                 | Mengelola versi Node.js untuk membangun aset frontend.                     |

### **3. Rincian Implementasi & Keputusan Desain**

#### **3.1. Inisialisasi Proyek & Repositori**

- **Aksi:** Proyek Laravel 12 baru (`MyRVM-Platform`) dibuat menggunakan Laravel Installer.
- **Keputusan Desain:**
  - **Starter Kit:** Dipilih **`[none]`** saat instalasi untuk mendapatkan instalasi Laravel yang bersih. Ini memungkinkan kita untuk secara manual mengkonfigurasi pendekatan frontend "Blade + Vue.js" (MPA), sesuai dengan rencana, daripada dipaksa menggunakan pendekatan SPA (Inertia).
  - **Repositori:** Sebuah monorepo `MySuperApps` diinisialisasi di GitHub untuk menampung semua sub-proyek, termasuk `MyRVM-Platform`. Masalah `non-fast-forward` awal saat push diselesaikan dengan `git pull --rebase` untuk menggabungkan riwayat commit lokal dan remote.

#### **3.2. Arsitektur Lingkungan Docker**

- **Aksi:** File `docker-compose.yml` dibuat untuk mendefinisikan empat layanan inti (`app`, `web`, `db`, `minio`) dan satu layanan bantu (`phpmyadmin` pada awalnya, kemudian dihapus).
- **Keputusan Desain:**
  - **Jaringan:** Sebuah jaringan bridge kustom (`myrvm_network`) dibuat untuk memungkinkan komunikasi antar kontainer menggunakan nama service (misalnya, `app` bisa terhubung ke `db`).
  - **Persistensi Data:** _Named volumes_ (`myrvm_postgres_data`, `myrvm_minio_data`) digunakan untuk memastikan data database dan file MinIO tetap ada bahkan jika kontainer dihapus.
  - **Volume Mount Kode:** Direktori proyek di host (`./`) di-mount ke `/var/www/html` di dalam kontainer `app` dan `web`. Ini adalah keputusan untuk **memaksimalkan efisiensi pengembangan**, memungkinkan perubahan kode lokal langsung terlihat di kontainer tanpa perlu me-rebuild image.

#### **3.3. Pembangunan Image Aplikasi (`Dockerfile`)**

- **Aksi:** `Dockerfile` kustom dibuat untuk membangun image layanan `app`.
- **Keputusan Desain:**
  - **Base Image:** Dipilih `php:8.3-fpm-alpine` untuk mendapatkan performa PHP 8.3 dalam image yang ringan.
  - **Instalasi Ekstensi:** Semua ekstensi PHP yang dibutuhkan (termasuk `pdo_pgsql` untuk PostgreSQL dan `imagick` untuk gambar) diinstal secara eksplisit.
  - **Skrip Entrypoint (`entrypoint.sh`):** Sebuah skrip entrypoint diimplementasikan untuk menangani tugas-tugas inisialisasi runtime. Ini adalah keputusan kunci untuk mengatasi masalah permission dan otomatisasi. Skrip ini bertugas:
    1.  Mengatur kepemilikan dan izin direktori `storage` dan `bootstrap/cache`.
    2.  Menjalankan `composer install` sebagai fallback.
    3.  Menjalankan perintah Artisan awal (`key:generate`, `migrate`).
    4.  Mengeksekusi proses utama (`php-fpm`) sebagai user non-root (`www-data`) menggunakan `su-exec`.

#### **3.4. Inisialisasi Database Otomatis (PostgreSQL)**

- **Aksi:** Skrip `init-db.sh` dibuat dan di-mount ke `/docker-entrypoint-initdb.d/` di dalam kontainer PostgreSQL.
- **Keputusan Desain:**
  - **Pemisahan Peran User:** Diputuskan untuk memisahkan **superuser PostgreSQL** (didefinisikan di `docker-compose.yml` `environment`) dari **user aplikasi** (`myrvm_user`, didefinisikan di `.env` Laravel).
  - **Kepemilikan Database:** Skrip `init-db.sh` secara eksplisit membuat database `myrvm_platform` dan menetapkan `myrvm_user` sebagai **pemiliknya (`OWNER`)**. Keputusan ini secara fundamental menyelesaikan masalah hak akses skema (`permission denied for schema public`) yang sering terjadi di PostgreSQL.

#### **3.5. Skema Database & Migrasi**

- **Aksi:** Semua file migrasi untuk skema database v2.1 dibuat, termasuk `tenants`, `roles`, `permissions`, `users` (yang dimodifikasi), `user_balances`, `transactions`, `vouchers`, dan `deposits`. Tabel `sessions` juga dibuat menggunakan `php artisan session:table`.
- **Keputusan Desain:**
  - **Urutan Migrasi:** Ditemukan bahwa urutan eksekusi migrasi sangat penting. Migrasi `users` yang memiliki foreign key ke `roles` harus memiliki timestamp yang lebih baru daripada migrasi `roles`. Nama file migrasi `users` diubah untuk memastikan urutan yang benar.
  - **Tipe Data:** Tipe data `decimal` dipilih untuk semua kolom finansial (`user_balances`, `transactions.amount`, `vouchers.cost`) untuk memastikan presisi.

### **4. Log Troubleshooting & Solusi**

Fase ini berhasil melewati beberapa tantangan teknis:

- **Masalah Build Docker:**
  - **`Cannot find autoconf`:** Diselesaikan dengan menambahkan `autoconf`, `automake`, `libtool`, `pkgconfig` ke `apk add` di `Dockerfile`.
  - **`composer.json not found`:** Diidentifikasi sebagai potensi masalah `.dockerignore` atau konteks build yang salah.
- **Masalah Runtime Kontainer:**
  - **Error Permission `storage/` & `bootstrap/cache`:** Diselesaikan secara robust menggunakan skrip `entrypoint.sh` yang menjalankan `chown` dan `chmod` saat kontainer dimulai.
  - **Error Startup PHP-FPM (`failed to open error_log`):** Diselesaikan dengan menimpa konfigurasi pool FPM (`www.conf`) untuk mengarahkan log error ke file di dalam `storage/logs/`.
- **Masalah Koneksi & Migrasi Database:**
  - **`role "root" does not exist`:** Diselesaikan dengan mengubah `DB_USERNAME` di `.env` menjadi `postgres` untuk koneksi awal.
  - **`password authentication failed`:** Diselesaikan dengan menyelaraskan password di `init-db.sh` dan `.env`.
  - **`relation "roles" does not exist`:** Diselesaikan dengan memperbaiki urutan (timestamp) file migrasi.
  - **`relation "sessions" does not exist`:** Diselesaikan dengan menjalankan `php artisan session:table` dan `php artisan migrate`.

### **5. Hasil Akhir Fase**

Pada akhir Fase 1, tumpukan teknologi backend telah berhasil di-deploy dan dijalankan di lingkungan Docker lokal. Aplikasi Laravel 12 berhasil menampilkan halaman defaultnya, terhubung ke database PostgreSQL yang skemanya telah dimigrasikan, dan siap untuk pengembangan logika bisnis di fase selanjutnya. Semua fondasi teknis yang diperlukan untuk `MyRVM-Platform` telah diletakkan dengan solid.
