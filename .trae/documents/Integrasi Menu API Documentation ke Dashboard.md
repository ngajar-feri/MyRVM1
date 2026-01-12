# Rencana Integrasi Menu API Documentation

## 1. Tujuan
Menambahkan akses cepat ke dokumentasi API (Swagger UI) langsung dari sidebar dashboard Admin/Tenant. Ini akan memudahkan pengembang dan pengguna teknis untuk menguji dan memahami endpoint API tanpa harus mengetik URL secara manual.

## 2. Lingkup Perubahan
*   **Frontend**: Modifikasi file `resources/views/layouts/app.blade.php`.
*   **Dokumentasi**: Update rencana kerja di `Docs/PLAN`.

## 3. Detail Implementasi
### A. Modifikasi Sidebar Menu
Kita akan menyisipkan item list (`<li>`) baru di bawah menu "Logs".

**Kode yang akan ditambahkan:**
```html
<li class="menu-item">
    <a href="{{ url('/api/documentation') }}" class="menu-link" target="_blank">
        <i class="menu-icon icon-base ti tabler-api"></i>
        <div>API Documentation</div>
    </a>
</li>
```
*   **Icon**: Menggunakan `tabler-api` (atau icon relevan lain jika library icon mendukungnya).
*   **Target**: `_blank` agar dokumentasi terbuka di tab baru, menjaga user tetap di dashboard.

### B. Dokumentasi Rencana
Membuat file `Docs/PLAN/api-integration-plan-Kamis-08Januari2026-1400.md` sesuai format yang diminta.

## 4. Rencana Pengujian
1.  **Visual Check**: Memastikan menu "API Documentation" muncul di sidebar.
2.  **Functional Check**: Klik menu tersebut dan pastikan membuka halaman Swagger UI (`/api/documentation`) dengan sukses (Status 200).
3.  **Role Check**: Memastikan menu ini terlihat oleh role yang sesuai (saat ini untuk semua user yang login).

Apakah Anda setuju dengan rencana penambahan menu ini?
