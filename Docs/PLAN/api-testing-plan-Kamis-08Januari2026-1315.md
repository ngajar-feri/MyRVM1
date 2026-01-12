Versi Dokumen: 1.0
Tanggal Revisi: Kamis-08 Januari 2026 - 01:15 PM
Tujuan: Mendokumentasikan prosedur pengujian Endpoint API dan perbaikan error 500 pada modul Voucher.
Status: Selesai

# Rencana Pengujian API & Troubleshooting

## 1. Lingkup Pengujian
Dokumen ini mencakup skenario pengujian untuk:
1.  **Public API**: Endpoint yang dapat diakses tanpa token (misal: Daftar Voucher).
2.  **Auth API**: Proses login untuk mendapatkan token JWT/Sanctum.
3.  **Protected API**: Endpoint yang memerlukan header `Authorization: Bearer <token>`.

## 2. Cara Menguji Endpoint (Apidog / Postman)

### A. Konfigurasi Environment
*   **Base URL**: `http://localhost:8000/api/v1`
*   **Headers**:
    *   `Accept`: `application/json`
    *   `Content-Type`: `application/json`

### B. Menguji Endpoint Voucher (Public)
**Request:**
*   **Method**: `GET`
*   **URL**: `{{base_url}}/vouchers`

**Ekspektasi Hasil:**
*   **Status 200 OK**: Jika database terhubung dan tabel ada.
    ```json
    {
        "status": "success",
        "data": [ ... ]
    }
    ```
*   **Status 500 Internal Server Error**: Jika tabel `vouchers` hilang atau koneksi DB putus.

### C. Menguji Login & Token (Auth)
**Request:**
*   **Method**: `POST`
*   **URL**: `{{base_url}}/login`
*   **Body (JSON)**:
    ```json
    {
        "email": "tenant@example.com",
        "password": "password",
        "device_name": "Apidog Client"
    }
    ```

**Ekspektasi Hasil:**
*   **Status 200 OK**: Mengembalikan token.
    ```json
    {
        "status": "success",
        "data": {
            "token": "1|laravel_sanctum_token...",
            "token_type": "Bearer"
        }
    }
    ```

## 3. Log Perubahan & Troubleshooting

### Isu: 500 Internal Server Error pada `/api/v1/vouchers`
*   **Analisis**: Log menunjukkan `SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "vouchers" does not exist`.
*   **Penyebab**: Migrasi database belum dijalankan di environment Docker.
*   **Solusi**:
    1.  Masuk ke container: `docker exec -it myrvm-app bash`
    2.  Jalankan migrasi: `php artisan migrate`
    3.  Verifikasi ulang endpoint.

### Rollback Plan
Jika migrasi menyebabkan konflik:
1.  Rollback batch terakhir: `php artisan migrate:rollback`
2.  Cek status migrasi: `php artisan migrate:status`

## 4. Referensi Dokumentasi API
Dokumentasi lengkap (Swagger UI) dapat diakses di:
`http://localhost:8000/api/documentation`
(Pastikan command `php artisan l5-swagger:generate` sudah dijalankan).
