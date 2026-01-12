Sangat baik. Berdasarkan diskusi dan keputusan terakhir kita untuk mengubah alur identifikasi pengguna, saya telah menyusun dokumentasi revisi yang komprehensif.

Dokumen ini akan menggantikan dan memperbarui rencana pengembangan sebelumnya, menjadi landasan baru untuk proyek MyRVM v1.0.

---

# **Dokumentasi Revisi & Rencana Pengembangan: MyRVM v2.1**

**Versi Dokumen:** ...
**Tanggal Revisi:** 22 Mei 2025
**Perubahan Fundamental:** Mengadopsi alur kerja **"Pengguna Memindai RVM"** untuk identifikasi sesi dan memperkenalkan mode **"Tamu/Donasi"** untuk menurunkan hambatan adopsi.

## **1. Ringkasan Perubahan Fundamental (Revisi 2.1)**

Dokumen ini merevisi **Rencana Pengembangan MyRVM v1.0** dengan perubahan krusial pada alur interaksi pengguna-mesin. Perubahan utama adalah:

- **Perubahan Alur Identifikasi:** Beralih dari Skenario "Mesin RVM Memindai QR Pengguna" ke **Skenario "Pengguna Memindai QR yang Ditampilkan di Layar RVM"**.
- **Pengenalan Mode Tamu/Donasi:** Mesin RVM sekarang dapat digunakan tanpa perlu registrasi atau login, di mana reward yang dihasilkan akan didonasikan.
- **Implikasi Teknologi:** Perubahan ini menghilangkan kebutuhan akan kamera khusus pemindai QR di RVM, namun menjadikan **layar sentuh** dan koneksi **WebSocket** sebagai komponen wajib untuk interaksi _real-time_.

## **2. Arsitektur dan Tumpukan Teknologi yang Disesuaikan**

Arsitektur 4 pilar tetap sama, namun peran dan teknologi beberapa komponen disesuaikan.

| Komponen                 | Teknologi / Framework                                  | Peran Utama yang Disesuaikan                                                                                                                                                 |
| :----------------------- | :----------------------------------------------------- | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Backend & Dasbor**     | Laravel 12, **Laravel Reverb (WebSocket)**, PostgreSQL | **Menyediakan API untuk manajemen sesi RVM**, mengelola state sesi (menunggu klaim, aktif), dan menyiarkan (_broadcast_) event melalui WebSocket.                            |
| **Aplikasi User**        | Flutter (Dart), **pustaka `qr_code_scanner`**          | **Menambahkan fitur pemindai QR** untuk mengklaim sesi di RVM. Menghapus fitur menampilkan QR code pengguna.                                                                 |
| **Aplikasi Kontrol RVM** | Python 3 (di Jetson Orin Nano)                         | **Menjadi "jembatan" hardware**, mendengarkan perintah dari backend via WebSocket, dan mengontrol perangkat keras (kamera objek, motor). Tidak lagi melakukan pemindaian QR. |
| **Antarmuka RVM (BARU)** | **Blade + Vue.js (disajikan oleh Backend)**            | **Menjadi antarmuka utama di layar sentuh RVM.** Menampilkan QR code sesi, merespons event WebSocket, dan menampilkan instruksi kepada pengguna.                             |

## **3. Alur Kerja Fungsional yang Baru**

### **3.1. Alur untuk Pengguna Terdaftar**

1.  **Inisiasi Sesi oleh RVM:**
    a. Antarmuka Web ("Front Office") di layar sentuh RVM, saat dalam mode siaga, secara otomatis meminta **token sesi unik** dari Backend (`GET /api/v2/rvm/session/create`).
    b. Backend membuat token sesi, menyimpannya di cache (misalnya Redis) dengan status "menunggu_otorisasi", dan mengirimkannya kembali.
    c. Antarmuka Web RVM menampilkan token sesi ini sebagai **QR Code besar**.
2.  **Klaim Sesi oleh Pengguna:**
    a. Pengguna membuka Aplikasi User (`MyRVM-UserApp`), login, dan memilih fitur "Pindai RVM".
    b. Menggunakan kamera ponsel, pengguna memindai QR Code di layar RVM.
    c. Aplikasi User mengirimkan **token sesi** yang dipindai (bersama dengan token otentikasi pengguna) ke Backend (`POST /api/v2/rvm/session/claim`).
3.  **Otorisasi & Aktivasi Sesi:**
    a. Backend memvalidasi token sesi dan token pengguna.
    b. Backend mengubah status sesi menjadi "diotorisasi*oleh_user_X" dan menyimpan `user_id`.
    c. Backend menyiarkan (\_broadcasts*) **event WebSocket** `SesiDiotorisasi` ke _channel_ privat untuk RVM tersebut, berisi nama pengguna.
4.  **Interaksi di RVM:**
    a. Antarmuka Web di layar RVM, yang mendengarkan di _channel_ tersebut, menerima event dan mengubah tampilan menjadi "Selamat Datang, [Nama Pengguna]! Silakan Masukkan Item".
    b. Aplikasi Python di Jetson (yang juga mendengarkan di _channel_ yang sama) menerima event dan memberi perintah ke ESP32 untuk membuka pintu.
5.  **Proses Deposit:** Alur selanjutnya (deteksi item, analisis AI, pemilahan) berjalan seperti yang telah dirancang, dengan komunikasi status selanjutnya juga melalui WebSocket.

### **3.2. Alur untuk Pengguna Tamu (Donasi)**

1.  **Inisiasi Sesi oleh RVM:** Sama seperti langkah 1 di atas, RVM menampilkan QR Code.
2.  **Memulai Sesi Tamu:**
    a. Di samping QR Code, layar RVM menampilkan tombol besar **"Lanjutkan sebagai Tamu (Donasi)"**.
    b. Pengguna menekan tombol ini di layar sentuh.
3.  **Aktivasi Sesi Tamu:**
    a. Antarmuka Web RVM mengirimkan **token sesi** yang sedang ditampilkan ke Backend (`POST /api/v2/rvm/session/activate-guest`).
    b. Backend mengubah status sesi menjadi "aktif_sebagai_tamu".
    c. Backend menyiarkan event WebSocket `SesiTamuAktif`.
4.  **Interaksi di RVM:**
    a. Antarmuka Web RVM menerima event dan mengubah tampilan menjadi "Mode Donasi Aktif. Silakan Masukkan Item".
    b. Aplikasi Python di Jetson menerima event dan membuka pintu.
5.  **Proses Deposit:** Saldo/poin yang dihasilkan dari sesi ini akan dialokasikan oleh backend ke akun donasi yang telah ditentukan.

## **4. Dampak pada Rencana Implementasi & Peta Jalan Progres**

### **Fase 1: Fondasi & Arsitektur Inti (Progres: 0% -> 25%)**

- **1.2. Konfigurasi Lingkungan Docker:** Perlu ditambahkan service untuk **Laravel Reverb** (server WebSocket) atau **Soketi** di `docker-compose.yml`.
- **1.5. Desain & Migrasi Skema Database:** Tidak ada perubahan besar, tetapi perlu dipastikan tabel `users` memiliki akun "sistem" untuk menampung poin donasi.

### **Fase 2: Backend - Autentikasi Multi-Peran & API Inti (Progres: 25% -> 55%)**

- **2.4. Pengembangan API CRUD Dasar:** Perlu ditambahkan endpoint-endpoint baru untuk manajemen sesi:
  - `GET /api/v2/rvm/session/create`
  - `POST /api/v2/rvm/session/claim`
  - `POST /api/v2/rvm/session/activate-guest`
- **Tugas Baru (2.5): Implementasi Backend WebSocket:**
  - Instal dan konfigurasikan **Laravel Reverb**.
  - Definisikan _broadcast channels_ (misalnya, `RvmChannel.{rvm_id}`).
  - Definisikan _broadcast events_ (misalnya, `SesiDiotorisasi`, `SesiTamuAktif`, `AnalisisSelesai`).
  - Implementasikan logika di controller untuk menyiarkan event ini setelah aksi yang relevan.

### **Fase 3: Edge AI & Aplikasi Kontrol RVM (`MyRVM-EdgeControl`) (Progres: 55% -> 70%)**

- **Perubahan Besar:**
  - **Hapus** semua logika pemindaian QR code (`pyzbar`).
  - **Tambahkan** implementasi **klien WebSocket** (misalnya, menggunakan pustaka `websockets` Python) yang akan terhubung ke server Laravel Reverb.
  - Aplikasi Python sekarang akan **mendengarkan event** dari backend untuk mengetahui kapan harus membuka pintu, menampilkan status, dll., dan **mengirim event** untuk melaporkan status sensor.

### **Fase 4: Pengembangan Frontend (Dasbor & Aplikasi Mobile) (Progres: 70% -> 95%)**

- **Tugas Baru (4.1b): Buat Antarmuka RVM "Front Office" (Blade + Vue):**
  - Buat rute web, controller, dan file Blade/Vue untuk antarmuka ini.
  - Implementasikan logika untuk meminta token sesi dan menampilkannya sebagai QR.
  - Implementasikan **klien Laravel Echo (JavaScript)** untuk mendengarkan event WebSocket dan memperbarui UI secara _real-time_.
- **4.3. Aplikasi User (`MyRVM-UserApp` - Flutter):**
  - **Hapus** semua UI dan logika untuk menampilkan QR code.
  - **Tambahkan** layar/fitur baru "Pindai RVM" yang menggunakan kamera ponsel untuk memindai QR.
  - Implementasikan logika untuk memanggil API `session/claim` setelah berhasil memindai.
- **4.2. Aplikasi Tenant (`MyRVM-TenantApp`):** Tidak terpengaruh secara langsung oleh perubahan ini.

### **Fase 5 & 6 (Ekonomi & Deployment):** Tidak ada perubahan fundamental, tetapi pengujian End-to-End di Fase 6 sekarang harus mencakup alur WebSocket yang baru.

---

Dokumen ini sekarang mencerminkan arsitektur yang lebih matang, andal, dan ramah pengguna. Perubahan ini memperkuat fondasi proyek untuk adopsi yang lebih luas. Saya siap menunggu perintah Anda untuk menyusun langkah-langkah implementasi yang detail berdasarkan rencana revisi ini.
