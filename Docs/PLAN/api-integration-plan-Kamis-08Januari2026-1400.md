Versi Dokumen: 1.0
Tanggal Revisi: Kamis-08 Januari 2026 - 02:00 PM
Tujuan: Mengintegrasikan akses dokumentasi API ke dalam dashboard utama untuk kemudahan akses developer dan tenant.
Status: Selesai

# Rencana Integrasi Menu API Documentation

## 1. Pendahuluan
Dokumen ini merinci langkah-langkah penambahan menu "API Documentation" pada sidebar dashboard aplikasi MyRVM. Menu ini akan mengarahkan pengguna ke halaman Swagger UI yang telah digenerate sebelumnya.

## 2. Lingkup Perubahan
*   **File Target**: `resources/views/layouts/app.blade.php`
*   **Lokasi Menu**: Di bawah menu "Logs" pada bagian "Monitoring".
*   **Target URL**: `/api/documentation` (route default L5-Swagger).

## 3. Spesifikasi Implementasi
### Struktur Menu Baru
```html
<li class="menu-item">
    <a href="{{ url('/api/documentation') }}" class="menu-link" target="_blank">
        <i class="menu-icon icon-base ti tabler-api"></i>
        <div>API Documentation</div>
    </a>
</li>
```

### UX Considerations
*   Menggunakan `target="_blank"` untuk membuka dokumentasi di tab baru agar user tidak kehilangan konteks halaman dashboard saat ini.
*   Menggunakan ikon `tabler-api` untuk representasi visual yang sesuai.

## 4. Log Perubahan (Changelog)
*   [08-01-2026 14:00] Pembuatan rencana integrasi.
*   [08-01-2026 14:05] Implementasi kode pada `app.blade.php`.

## 5. Rencana Rollback
Jika terjadi kesalahan tampilan (misal: layout sidebar rusak):
1.  Revert perubahan pada `resources/views/layouts/app.blade.php`.
2.  Hapus item `<li>` yang baru ditambahkan.
