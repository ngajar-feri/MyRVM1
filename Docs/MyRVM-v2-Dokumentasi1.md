# **Dokumentasi Perubahan & Rencana Pengembangan: MyRVM v2.0**

**Versi Dokumen:** ...
**Tanggal Revisi:** 22 Mei 2025
**Tujuan:** Mendefinisikan ulang arsitektur, teknologi, dan cakupan fungsional Proyek MyRVM untuk bertransformasi menjadi **Platform Ekonomi Daur Ulang (Recycling Economy Platform)** yang komprehensif, multi-tenant, dan didukung oleh AI di perangkat (_edge AI_).

## **1. Ringkasan Perubahan Fundamental**

Proyek MyRVM v2.0 berevolusi dari sistem RVM cerdas tunggal menjadi sebuah platform multi-lapis yang mencakup:

- **Pergeseran AI:** Dari AI berbasis Cloud (Google Gemini) ke **AI di Perangkat (_Edge AI_)** menggunakan model Computer Vision (YOLO + SAM) yang berjalan langsung di mesin RVM.
- **Perluasan Model Pengguna:** Dari model pengguna sederhana menjadi arsitektur **multi-tenant** yang mendukung berbagai peran (Super Admin, Admin, Tenant, User).
- **Evolusi Sistem Reward:** Dari sistem poin sederhana menjadi **ekonomi mikro internal** dengan saldo, voucher, dan visi jangka panjang untuk token yang dipatok ke aset (stablecoin).
- **Peningkatan Teknologi:** Adopsi **Laravel 12** dan **PostgreSQL** sebagai fondasi backend, serta perubahan pendekatan frontend dari SPA (Inertia) ke **MPA dengan Vue.js dan Blade Templates**.
- **Ekspansi Metode Login:** Menambahkan dukungan untuk **LINE** dan **Discord** di samping login Email dan Google.

## **2. Arsitektur dan Tumpukan Teknologi yang Diperbarui**

| Komponen                       | Teknologi / Framework                            | Database           | Peran Utama                                                                                  |
| :----------------------------- | :----------------------------------------------- | :----------------- | :------------------------------------------------------------------------------------------- |
| **Backend & Dasbor Admin**     | Laravel 12, PHP 8.2+, Vue.js, Blade Templates    | **PostgreSQL 17+** | Otak pusat, API, dasbor multi-peran, manajemen tenant & voucher, ledger saldo.               |
| **Aplikasi User (Mobile)**     | Flutter (Dart)                                   | -                  | Antarmuka untuk pengguna akhir (melihat saldo, menukar voucher, mendapatkan QR code).        |
| **Aplikasi Tenant (Mobile)**   | Flutter (Dart)                                   | -                  | Dasbor versi mobile untuk tenant (melihat penukaran voucher, mengelola penawaran).           |
| **Aplikasi Kontrol RVM**       | Python 3 (di Jetson Orin Nano)                   | -                  | Otak di lapangan (_edge computing_), menjalankan model AI lokal, mengontrol perangkat keras. |
| **Computer Vision (Lokal)**    | **YOLOv11/v12 + SAMv2 (PyTorch)**                | -                  | Inferensi AI di perangkat untuk deteksi & segmentasi objek secara _real-time_.               |
| **AI (Cloud - Sekunder)**      | Google Gemini API                                | -                  | Digunakan untuk validasi sekunder atau analisis data lanjutan di dasbor.                     |
| **Deployment & Infrastruktur** | Docker, Docker Compose, Nginx, PostgreSQL, MinIO | PostgreSQL         | Mengemas dan menjalankan semua layanan backend di server.                                    |

## **3. Manajemen Repositori & Akses**

Untuk mendukung arsitektur yang diperluas, repositori akan diatur sebagai berikut di GitHub (`github.com/vnot01`):

| Nama Repositori         | URL Repositori                                | Deskripsi                                                                                 | Komponen Terkait                             |
| :---------------------- | :-------------------------------------------- | :---------------------------------------------------------------------------------------- | :------------------------------------------- |
| **`MyRVM-Platform`**    | `https://github.com/vnot01/MyRVM-Platform`    | **Repositori Utama.** Berisi backend Laravel, dasbor admin, dan semua konfigurasi Docker. | Backend, Dasbor (Super Admin, Admin, Tenant) |
| **`MyRVM-UserApp`**     | `https://github.com/vnot01/MyRVM-UserApp`     | Aplikasi mobile Flutter untuk pengguna akhir.                                             | Aplikasi User (Mobile)                       |
| **`MyRVM-TenantApp`**   | `https://github.com/vnot01/MyRVM-TenantApp`   | Aplikasi mobile Flutter untuk tenant.                                                     | Aplikasi Tenant (Mobile)                     |
| **`MyRVM-EdgeControl`** | `https://github.com/vnot01/MyRVM-EdgeControl` | Kode Python untuk Jetson Orin Nano, termasuk model AI dan logika kontrol.                 | Aplikasi Kontrol RVM & Computer Vision Lokal |

**Cara Akses:**

- Pengembang akan meng-clone repositori yang relevan untuk pekerjaan mereka.
- Repositori `MyRVM-Platform` akan menjadi satu-satunya yang di-deploy ke server `docker-host`.
- Aplikasi mobile akan di-build dari `MyRVM-UserApp` dan `MyRVM-TenantApp` dan didistribusikan melalui app store atau metode lain.
- Kode dari `MyRVM-EdgeControl` akan di-deploy ke setiap unit mesin RVM (Jetson Orin Nano).

## **4. Detail Perubahan Fungsional**

### **4.1. Hierarki Pengguna & Dasbor**

Sistem akan memiliki beberapa peran dengan dasbor yang disesuaikan:

- **Super Admin:** Melihat semua data di semua tenant. Mengelola admin dan tenant. Dasbor analitik global.
- **Admin:** Mengelola semua data dalam satu tenant (jika sistem dibuat multi-tenant di level ini). Mengelola user dan RVM di bawah tenant tersebut.
- **Tenant:** Perusahaan/mitra yang mendaftar untuk menyediakan voucher. Mereka hanya bisa melihat dasbor analitik terkait penukaran voucher mereka dan mengelola profil mereka.
- **User:** Pengguna akhir yang menyetorkan sampah. Mereka berinteraksi melalui aplikasi mobile.

### **4.2. Alur Computer Vision (Edge-First)**

1.  **Pengambilan Gambar:** RVM mengambil gambar item.
2.  **Inferensi Lokal:** Aplikasi Python di Jetson Orin Nano:
    a. Menjalankan model **YOLO** untuk mendapatkan _bounding box_ dan klasifikasi awal.
    b. Menjalankan model **SAM** pada _bounding box_ untuk mendapatkan _segmentation mask_.
    c. Menganalisis _mask_ untuk fitur tambahan (kondisi, volume).
3.  **Pengiriman Hasil:** RVM mengirim **hasil analisis terstruktur (JSON)** ke backend Laravel, bukan lagi file gambar mentah.
4.  **Validasi Sekunder (Opsional):** Jika kepercayaan model lokal rendah, backend dapat memutuskan untuk mengirim gambar asli ke **Google Gemini API** untuk verifikasi.

### **4.3. Alur Login Sosial (LINE & Discord)**

1.  Aplikasi frontend (mobile atau web) akan menggunakan SDK LINE/Discord untuk mendapatkan _authorization code_ dari pengguna.
2.  _Authorization code_ ini dikirim ke backend Laravel.
3.  Backend (menggunakan Laravel Socialite dengan driver kustom/pihak ketiga) akan menukar _code_ tersebut dengan _access token_ dan mengambil profil pengguna dari API LINE/Discord.
4.  Backend akan membuat atau login pengguna di database lokal dan mengembalikan token sesi (Sanctum) ke frontend.

### **4.4. Alur Ekonomi Reward**

1.  **Pemberian Saldo:** Saat user berhasil menyetorkan item, backend akan menambahkan nilai (misalnya, setara Rupiah) ke kolom `balance` di tabel `users`, dan mencatatnya di tabel `transactions`.
2.  **Penukaran Voucher:**
    a. User memilih voucher dari tenant di Aplikasi User.
    b. Aplikasi memeriksa apakah saldo user mencukupi.
    c. Backend membuat transaksi debit dari saldo user, mencatat penukaran, dan menghasilkan kode voucher unik.
    d. Stok voucher di database tenant berkurang.
3.  **Visi Stablecoin:**
    a. Nilai saldo pengguna secara internal dipatok ke nilai 1 gram emas (menggunakan _oracle_ harga).
    b. Dana yang masuk dari tenant atau sumber lain akan diinvestasikan secara transparan di lembaga keuangan yang diakui (misalnya, Tabungan Emas Pegadaian) untuk mendukung nilai token internal. Ini membangun kepercayaan dan ekosistem yang berkelanjutan.

## **5. Perubahan pada Skema Database (Konsep Awal)**

- **Tabel `users`:** Akan memiliki `role_id` (FK) dan `tenant_id` (FK, nullable).
- **Tabel Baru:** `roles`, `permissions`, `tenants`.
- **Tabel `deposits`:** Kolom `image_path` akan menjadi opsional, digantikan oleh `local_ai_result` (JSON).
- **Tabel Baru (Ekonomi):** `user_balances`, `transactions` (dengan tipe debit/kredit), `tenants`, `vouchers`, `voucher_redemptions`.
