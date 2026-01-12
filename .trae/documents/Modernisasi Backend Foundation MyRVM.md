# Rencana Pembaruan Backend Foundation & Dokumentasi

Berdasarkan evaluasi terhadap dokumen lama di `Docs/Overview1` dan kondisi codebase saat ini, ditemukan kesenjangan signifikan (gap) antara rencana arsitektur dengan implementasi. Berikut adalah rencana kerja untuk menyelaraskan keduanya:

## 1. Pembaruan Dokumentasi (Scope & Timeline)
*   **Dokumen Baru**: Membuat `Docs/PLAN/backend-foundation-plan-[timestamp].md`.
*   **Konten**:
    *   Menyatakan bahwa `Docs/Overview1` adalah referensi konseptual lama.
    *   Menetapkan roadmap baru: Fase 1 (Foundation), Fase 2 (Core Modules), Fase 3 (Advanced Integration).
    *   Memperbarui diagram arsitektur API (memisahkan Web Routes vs API Routes).

## 2. Pengembangan Backend Foundation (MyRVM-Server)
Fokus utama adalah mengubah aplikasi Monolith saat ini menjadi **Hybrid (Monolith + API Service)** yang siap melayani Mobile Apps dan IoT Devices.

### A. Infrastruktur API
1.  **Instalasi API Scaffolding**: Menjalankan `php artisan install:api` untuk mengaktifkan `routes/api.php` dan Sanctum (Token Auth).
2.  **Konfigurasi Sanctum**: Memastikan model `User` menggunakan trait `HasApiTokens`.

### B. Autentikasi & Keamanan
1.  **API Auth Controller**: Membuat controller khusus (`Api/AuthController`) untuk login via API yang mengembalikan JSON token (bukan redirect/session).
2.  **Rate Limiting**: Mengonfigurasi `ThrottleRequests` di `bootstrap/app.php` untuk mencegah abuse (misal: 60 request/menit).

### C. Dokumentasi & Manajemen (API Management)
1.  **Swagger/OpenAPI**: Menginstal `l5-swagger` agar dokumentasi API tergenerate otomatis dari anotasi kode. Ini krusial untuk tim Mobile/IoT.
2.  **Logging Middleware**: Membuat middleware sederhana untuk mencatat setiap request/response API ke tabel `activity_logs` (untuk monitoring).

### D. Refactoring Existing Code
1.  **Migrasi Route**: Memindahkan endpoint `/api/vouchers` yang saat ini "nyasar" di `web.php` ke `api.php` dengan versioning (`/api/v1/vouchers`).
2.  **Standardisasi Response**: Membuat Helper/Trait `ApiResponse` agar semua output JSON memiliki format konsisten `{ status, message, data, errors }`.

## 3. Tahapan Eksekusi
1.  Update Dokumentasi Rencana (sebagai panduan acuan).
2.  Setup Teknis (Install API, Sanctum, Swagger).
3.  Implementasi Auth & Security.
4.  Refactor modul Voucher sebagai "Pilot Project" API.
5.  Validasi endpoint menggunakan Swagger UI.

Apakah Anda setuju dengan rencana modernisasi backend ini?
