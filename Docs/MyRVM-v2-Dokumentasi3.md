# **Dokumentasi Pengembangan: MyRVM v1.1**

## **Fase 1: Fondasi & Arsitektur Inti**

**Status Fase:** **SELESAI**
**Progres Proyek (Setelah Fase 1):** 30%
**Tanggal Penyelesaian Fase:** 22 Mei 2025

### **1. Tujuan Fase**

Tujuan utama dari fase ini adalah untuk membangun dan memvalidasi seluruh fondasi teknis untuk backend `MyRVM-Platform`. Ini mencakup inisialisasi proyek, penyiapan lingkungan pengembangan berbasis Docker yang terisolasi dan portabel, serta pembuatan skema database baru yang akan mendukung semua fitur yang direncanakan, termasuk fungsionalitas WebSocket.

### **2. Komponen & Teknologi yang Digunakan**

| Komponen                  | Teknologi / Versi Digunakan                | Tujuan                                                                     |
| :------------------------ | :----------------------------------------- | :------------------------------------------------------------------------- |
| **Backend Framework**     | Laravel 12.x                               | Menyediakan struktur aplikasi, routing, ORM, dan API.                      |
| **Bahasa Pemrograman**    | PHP 8.3                                    | Menjalankan logika backend, dipilih untuk fitur modern dan kompatibilitas. |
| **Database**              | PostgreSQL (Image `postgres:latest`, v17+) | Menyimpan semua data aplikasi secara persisten.                            |
| **Web Server**            | Nginx (Image `nginx:alpine`)               | Melayani request HTTP dan meneruskan request PHP ke PHP-FPM.               |
| **Penyimpanan Objek**     | MinIO (Image `minio/minio:latest`)         | Menyediakan penyimpanan file S3-compatible untuk aset seperti avatar.      |
| **Server WebSocket**      | Laravel Reverb                             | Menangani koneksi dan komunikasi real-time.                                |
| **Containerization**      | Docker & Docker Compose                    | Mengemas, mengisolasi, dan mengorkestrasi semua layanan backend.           |
| **Manajemen Repositori**  | Git & GitHub (Monorepo `MySuperApps`)      | Mengelola dan melacak perubahan kode sumber.                               |
| **Manajemen Konfigurasi** | `.env` & `git-crypt` (direncanakan)        | Mengelola variabel lingkungan dan rahasia secara aman.                     |

### **3. Rincian Implementasi & Keputusan Desain**

#### **3.1. Inisialisasi Proyek & Repositori**

- **Aksi:** Proyek `MyRVM-Platform` (Laravel 12) dibuat dan diinisialisasi di dalam monorepo `MySuperApps`.
- **Keputusan Desain:**
  - **Starter Kit:** Dipilih **`[none]`** saat instalasi untuk kontrol penuh, dengan rencana membangun frontend MPA menggunakan **Blade dan Vue.js**.
  - **Manajemen Rahasia:** Direkomendasikan penggunaan **`git-crypt`** untuk mengenkripsi file `.env` di dalam repositori private, memastikan keamanan konfigurasi.

#### **3.2. Arsitektur Lingkungan Docker**

- **Aksi:** File `docker-compose.yml` dibuat untuk mendefinisikan lima layanan inti: `app`, `web`, `db`, `minio`, dan `reverb`.
- **Keputusan Desain:**
  - **Jaringan:** Sebuah jaringan bridge kustom (`myrvm_network`) dibuat untuk komunikasi internal antar kontainer menggunakan nama service.
  - **Persistensi Data:** _Named volumes_ digunakan untuk data PostgreSQL dan MinIO.
  - **Volume Mount Kode:** Direktori proyek di host di-mount ke kontainer `app` dan `web` untuk efisiensi pengembangan.

#### **3.3. Pembangunan Image Aplikasi (`Dockerfile`)**

- **Aksi:** `Dockerfile` kustom dibuat untuk membangun image layanan `app` dan `reverb`.
- **Keputusan Desain:**
  - **Base Image:** Dipilih `php:8.3-fpm-alpine` untuk mendapatkan performa PHP 8.3 dalam image yang ringan dan siap untuk masa depan.
  - **Ekstensi:** Semua ekstensi PHP yang dibutuhkan (termasuk `pdo_pgsql`, `imagick`, dan `pcntl` untuk Reverb) diinstal secara eksplisit.
  - **Skrip Entrypoint (`entrypoint.sh`):** Sebuah skrip entrypoint diimplementasikan untuk menangani:
    1.  **Sinkronisasi Startup:** Menunggu layanan database siap sebelum menjalankan perintah Artisan.
    2.  **Manajemen Permission:** Mengatur kepemilikan dan izin direktori `storage` dan `bootstrap/cache` secara otomatis.
    3.  **Inisialisasi Otomatis:** Menjalankan `php artisan key:generate` dan `php artisan migrate` pada startup pertama.
  - **Konfigurasi FPM:** File konfigurasi FPM kustom (`www.conf`) disalin ke dalam image untuk mengarahkan log error ke file, menyelesaikan masalah startup FPM.

#### **3.4. Inisialisasi & Skema Database (PostgreSQL)**

- **Aksi:** Skema database lengkap untuk v2.1 dibuat melalui file migrasi Laravel.
- **Keputusan Desain:**
  - **Inisialisasi Otomatis:** Proses inisialisasi database disederhanakan dengan mengandalkan **mekanisme bawaan dari image Docker `postgres`**. Variabel `POSTGRES_DB`, `POSTGRES_USER`, dan `POSTGRES_PASSWORD` di `docker-compose.yml` digunakan untuk secara otomatis membuat database dan user aplikasi, sekaligus menjadikannya sebagai pemilik database.
  - **Urutan Migrasi:** Urutan file migrasi disesuaikan dengan mengubah timestamp untuk memastikan tabel-tabel dasar (`tenants`, `roles`) dibuat sebelum tabel yang memiliki foreign key ke mereka (`users`).
  - **Model Eloquent:** Semua model yang relevan (`Tenant`, `Role`, `User`, `UserBalance`, `Transaction`, `Voucher`, `Deposit`, dll.) dibuat dan dikonfigurasi dengan properti `$fillable`, `$casts`, dan relasi Eloquent yang sesuai.

#### **3.5. Fondasi WebSocket (Laravel Reverb)**

- **Aksi:** Laravel Reverb diinstal dan dikonfigurasi. Sebuah service Docker `reverb` baru ditambahkan ke `docker-compose.yml`.
- **Keputusan Desain:**
  - **Definisi Channel & Event:** _Broadcast channel_ dinamis (`rvm.{rvmId}`) dan kelas-kelas _Event_ (`SesiDiotorisasi`, `AnalisisSelesai`, dll.) telah didefinisikan di backend, menyiapkan fondasi untuk komunikasi real-time.
  - **Otorisasi Channel:** Logika otorisasi awal di `routes/channels.php` diimplementasikan untuk memvalidasi permintaan berlangganan ke channel privat.

### **4. Hasil Akhir Fase**

Pada akhir Fase 1, seluruh fondasi backend untuk MyRVM v1.1 telah berhasil dibangun dan divalidasi. Stack Docker yang terdiri dari Laravel, Nginx, PostgreSQL, MinIO, dan Reverb berjalan secara stabil. Aplikasi Laravel dapat terhubung ke database, skema telah dimigrasikan, dan server WebSocket aktif dan siap menerima koneksi. Proyek ini sekarang memiliki dasar yang kuat dan terstruktur untuk melanjutkan ke pengembangan logika bisnis dan API di fase berikutnya.

---

**[CATATAN LAINNYA Fase 1]**

1.  **Struktur Repositori & Keamanan Konfigurasi:**
    - **Struktur:** Diputuskan untuk menggunakan satu repositori Git utama (monorepo) `MySuperApps` yang akan berisi semua sub-proyek (`MyRVM-Platform`, `MyRVM-UserApp`, dll.).
    - **Keamanan:** Untuk menyimpan file konfigurasi (`.env`) yang berisi rahasia di dalam repositori private, metode yang direkomendasikan adalah menggunakan **`git-crypt`**. Alat ini akan mengenkripsi file-file sensitif sebelum di-push ke GitHub dan mendekripsinya secara otomatis di mesin lokal developer yang memiliki kunci.
2.  **Manajemen Versi Node.js:**
    - **Alat:** **NVM (Node Version Manager)** akan digunakan di mesin pengembangan lokal dan server host untuk mengelola beberapa versi Node.js.
    - **Instalasi & Penggunaan:** Dicatat perintah instalasi (`curl ...`) dan perintah penggunaan dasar (`nvm install`, `nvm use`, `nvm alias default`) untuk referensi.
3.  **Troubleshooting Build Docker (`pecl install imagick`):**
    - **Masalah:** Build `Dockerfile` gagal saat `pecl install imagick` dengan error `Cannot find autoconf. ERROR: 'phpize' failed`.
    - **Solusi:** Menambahkan paket-paket build esensial (`autoconf`, `automake`, `libtool`, `pkgconfig`) ke dalam daftar instalasi `apk add` di `Dockerfile`.
4.  **Troubleshooting Inisialisasi Database PostgreSQL:**
    - **Masalah 1:** `php artisan migrate` gagal dengan `permission denied for schema public`.
      - **Penyebab:** User aplikasi tidak memiliki hak `CREATE` pada skema `public` karena bukan pemilik database.
      - **Solusi:** Menggunakan mekanisme inisialisasi bawaan dari image Docker `postgres`. Di `docker-compose.yml`, service `db` dikonfigurasi dengan `POSTGRES_DB`, `POSTGRES_USER`, dan `POSTGRES_PASSWORD` menggunakan kredensial aplikasi. Ini secara otomatis membuat database dan menjadikan user aplikasi sebagai pemiliknya. Skrip `init-db.sh` yang kompleks dihapus.
    - **Masalah 2:** Terjadi _race condition_ di mana kontainer `app` mencoba mengakses database sebelum kontainer `db` selesai inisialisasi.
      - **Penyebab:** `depends_on` hanya menunggu kontainer dimulai, bukan sampai layanan di dalamnya siap.
      - **Solusi:** Mengimplementasikan skrip "tunggu database" di dalam `entrypoint.sh` kontainer `app`. Skrip ini menggunakan `nc` untuk menunggu port terbuka, dan `psql` untuk menunggu koneksi ke database spesifik berhasil. Ini mengharuskan `postgresql-client` diinstal di dalam image `app`.
5.  **Troubleshooting Runtime PHP-FPM:**
    - **Masalah:** Kontainer `app` terus restart dengan error `FPM initialization failed` dan `failed to open error_log (/proc/self/fd/2): Permission denied`.
    - **Solusi:** Membuat file konfigurasi pool FPM kustom (`docker/php/www.conf`) yang secara eksplisit mengarahkan `error_log` ke file di dalam direktori `storage/logs/`. File konfigurasi ini kemudian di-`COPY` ke dalam image Docker untuk menimpa konfigurasi default.
