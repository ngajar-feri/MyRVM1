# Artifact: Diskusi Keamanan URL Kiosk
**Date:** 2026-01-26
**Context:** Diskusi arsitektur keamanan untuk URL endpoint Kiosk RVM-UI

---

## Latar Belakang

URL Kiosk (`/rvm-ui/{identifier}`) perlu diamankan agar:
1. Tidak mudah ditebak oleh orang iseng
2. Tidak bisa dipalsukan/dimodifikasi
3. Hanya bisa diakses oleh mesin RVM yang sah

---

## Opsi Keamanan yang Dibahas

### Opsi 1: Enkripsi Serial Number (Laravel Crypt)

**Cara Kerja:** Mengenkripsi string Serial Number menggunakan `Crypt::encryptString()`.

**Format URL:**
```
/rvm-ui/eyJpdiI6Im9heFo2RnFjdUZXeHhDWTBFUW8rbkE9PSIsInZhbHVlIjoidVdobUN5UzRqYzJ...
```

| Kelebihan | Kekurangan |
|-----------|------------|
| Sangat aman, tidak bisa ditebak tanpa `APP_KEY` | URL sangat panjang (100+ karakter) |
| Native Laravel, tidak perlu library tambahan | Tidak bisa diketik manual/dihafal |
| Otomatis invalid jika `APP_KEY` berubah | Sulit untuk debugging |

**Kesimpulan:** ❌ Tidak praktis untuk URL publik.

---

### Opsi 2: UUID (Universally Unique Identifier) ⭐ Direkomendasikan

**Cara Kerja:** Menambahkan kolom `uuid` (UUID v4) ke tabel `rvm_machines`. UUID di-generate otomatis saat record dibuat.

**Format URL:**
```
/rvm-ui/9fed2bc0-2b15-4c6e-9824-7f283287319c
```

| Kelebihan | Kekurangan |
|-----------|------------|
| Standar industri (RFC 4122) | Masih bisa diakses siapa saja yang punya link |
| Panjang URL wajar (36 karakter) | - |
| Tidak berurutan, tidak bisa ditebak | - |
| Terlihat profesional | - |

**Mapping Identitas:**
| Field | Kegunaan | Contoh |
|-------|----------|--------|
| `id` | Internal Database (FK, Index) | `1`, `2`, `3` |
| `serial_number` | Human Readable Label | `RVM-GI-001` |
| `uuid` | Public URL / API Endpoint | `9fed2bc0-...` |

**Kesimpulan:** ✅ Solusi seimbang antara keamanan dan kemudahan.

---

### Opsi 3: Hashids (Obfuscation)

**Cara Kerja:** Mengubah ID integer menjadi string pendek acak menggunakan library Hashids.

**Format URL:**
```
/rvm-ui/jR8bL
```

| Kelebihan | Kekurangan |
|-----------|------------|
| URL pendek dan bersih | Kurang cocok jika lookup sudah berbasis string |
| Reversible (bisa decode balik) | Butuh library tambahan |

**Kesimpulan:** ⚠️ Kurang cocok untuk arsitektur saat ini.

---

### Opsi 4: Signed URLs (Tanda Tangan Digital) ⭐ Tambahan Keamanan

**Cara Kerja:** Laravel membuat signature (hash) dari URL + `APP_KEY`. Jika URL dimodifikasi, signature tidak cocok dan request ditolak.

**Format URL:**
```
/rvm-ui/9fed2bc0-...?expires=1706234400&signature=a8f9c2d1e3b4...
```

| Kelebihan | Kekurangan |
|-----------|------------|
| URL tidak bisa dipalsukan | URL lebih panjang (ada query string) |
| Bisa diberi expiry time | Perlu regenerate jika expired |
| Native Laravel (`URL::signedRoute`) | - |

**Kesimpulan:** ✅ Layer keamanan tambahan di atas UUID.

---

### Opsi 5: IP Allowlist (Tailscale VPN)

**Cara Kerja:** Middleware mengecek apakah request berasal dari IP yang terdaftar (misalnya IP Tailscale `100.x.x.x`).

| Kelebihan | Kekurangan |
|-----------|------------|
| Keamanan sangat tinggi | Butuh maintenance daftar IP |
| Cocok untuk jaringan tertutup | IP bisa berubah (DHCP) |

**Kesimpulan:** ⚠️ Opsional, untuk keamanan ekstra di production.

---

## Keputusan Arsitektur

**Implementasi yang Dipilih:** **UUID + Signed URL**

### Alur Kerja:
1. Admin membuat mesin baru → Sistem generate UUID otomatis
2. Admin generate Signed URL dari Dashboard
3. URL dicopy ke konfigurasi Chromium Kiosk di Jetson
4. Request masuk → Server validasi signature → Jika valid, render Kiosk

### Keamanan yang Dicapai:
- ✅ ID tidak berurutan (UUID)
- ✅ URL tidak bisa dimodifikasi (Signed)
- ✅ Bisa diberi expiry (Signed + Expiration)
- ✅ Tidak perlu allowlist IP

---

## Perbandingan dengan E2E Encryption

| Aspek | E2E Encryption | Signed URL |
|-------|----------------|------------|
| Tujuan | Melindungi isi pesan | Memvalidasi asal URL |
| Cocok untuk | Chat, Transfer Data | Web URL, API Endpoint |
| Implementasi | Kompleks | Simple (Native Laravel) |

**Catatan:** HTTPS (SSL) sudah menyediakan enkripsi data in-transit. Signed URL menambahkan layer autentikasi origin.

---

## Referensi Implementasi

```php
// Generate Signed URL
use Illuminate\Support\Facades\URL;

$url = URL::signedRoute('kiosk.index', [
    'machine_uuid' => $machine->uuid
]);

// Dengan Expiry (1 jam)
$url = URL::temporarySignedRoute('kiosk.index', now()->addHour(), [
    'machine_uuid' => $machine->uuid
]);
```

```php
// Validasi di Controller/Middleware
if (! $request->hasValidSignature()) {
    abort(403, 'URL tidak valid atau sudah expired.');
}
```

---

**Status:** Pending Implementation
