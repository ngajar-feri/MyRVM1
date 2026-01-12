# Rencana Integrasi Iframe Swagger

## 1. Tujuan
Menampilkan halaman dokumentasi API (Swagger UI) langsung di dalam layout dashboard aplikasi, bukan membuka tab baru. Ini memberikan pengalaman pengguna yang lebih terintegrasi.

## 2. Solusi Teknis
Kita akan menggunakan teknik **Iframe Embedding**.
*   **Halaman Pembungkus**: Membuat halaman baru di dashboard yang memuat iframe.
*   **Sumber Iframe**: URL `/api/documentation`.
*   **Keamanan**: Memastikan server mengizinkan iframe (mengatur header `X-Frame-Options` atau `Content-Security-Policy`).

## 3. Langkah Implementasi

### A. Backend & Middleware
1.  **Middleware `AllowIframe`**: Membuat middleware khusus untuk menghapus header yang memblokir iframe (`X-Frame-Options`) dan menambahkan header izin (`Content-Security-Policy: frame-ancestors 'self'`).
2.  **Registrasi Middleware**: Mendaftarkan middleware ini di `config/l5-swagger.php` agar hanya berlaku untuk route dokumentasi.

### B. Frontend (View & Route)
1.  **View Baru**: Membuat `resources/views/dashboard/api-docs.blade.php`.
    *   Isinya: Layout dashboard standar (`@extends('layouts.app')`) dengan elemen `<iframe>` yang memenuhi area konten.
2.  **Route Baru**: Menambahkan route di `routes/web.php`:
    ```php
    Route::get('/dashboard/api-docs', function () {
        return view('dashboard.api-docs');
    })->middleware('auth')->name('dashboard.api-docs');
    ```
3.  **Update Menu**: Mengubah link di sidebar (`layouts/app.blade.php`) untuk mengarah ke route baru ini dan menghapus atribut `target="_blank"`.

## 4. Keuntungan
*   User tetap berada di dalam aplikasi.
*   Navigasi sidebar tetap terlihat dan berfungsi.
*   Tampilan lebih profesional.

Apakah Anda setuju dengan pendekatan iframe ini?
