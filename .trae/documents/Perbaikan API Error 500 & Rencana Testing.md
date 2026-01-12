# Rencana Perbaikan API & Dokumentasi Testing

Berikut adalah rencana kerja untuk mengatasi error 500 pada API Voucher dan mendokumentasikan prosedur pengujiannya.

## 1. Perbaikan Teknis (Fixing 500 Error)
Penyebab utama error 500 adalah **tabel `vouchers` yang belum terbentuk di database**.
*   **Action**: Menjalankan migrasi database di dalam container Docker.
*   **Verifikasi**: Memastikan endpoint `/api/v1/vouchers` mengembalikan JSON kosong (status 200) alih-alih error 500.

## 2. Dokumentasi API (Swagger/OpenAPI)
Agar API mudah diuji menggunakan tools seperti Apidog atau Postman, kita perlu dokumentasi standar.
*   **Action**: Menambahkan anotasi `@OA\...` pada `VoucherController` dan `AuthController`.
*   **Output**: Halaman dokumentasi interaktif di `/api/documentation`.

## 3. Dokumentasi Rencana Pengujian
Sesuai permintaan, saya akan membuat dokumen di `Docs/PLAN/api-testing-plan-[timestamp].md`.

### Struktur Dokumen
*   **Versi & Revisi**: Mengikuti format yang diminta.
*   **Lingkup**: Pengujian Endpoint Publik (Voucher) dan Protected (Auth).
*   **Cara Menguji**:
    1.  **Via CLI**: Menggunakan perintah `curl`.
    2.  **Via GUI**: Menggunakan Apidog/Postman (dengan import Swagger).
*   **Skenario Test**:
    *   Positive Case (200 OK)
    *   Negative Case (401 Unauthorized, 422 Validation Error)

## 4. Tahapan Eksekusi
1.  **Dokumentasi**: Membuat file `Docs/PLAN/api-testing-plan-Kamis-08Januari2026-1315.md`.
2.  **Fix Database**: Menjalankan `php artisan migrate`.
3.  **Implementasi Swagger**: Edit Controller & Generate Docs.
4.  **Verifikasi Akhir**: Test endpoint dengan `curl`.

Apakah Anda setuju dengan rencana perbaikan dan dokumentasi ini?
