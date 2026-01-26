# Artifact: MyRVM-Server Security Design
**Date:** 2026-01-26
**Revision Sequence:** 1.0
**Context:** Dokumentasi Infrastruktur Keamanan Terintegrasi

---

## 1. Authentication Layers

MyRVM-Server menerapkan strategi keamanan berlapis (Defense in Depth) berdasarkan profil user/perangkat:

### A. Perangkat Edge (Machine-to-Server)
- **Mechanism:** API Key Validation.
- **Header:** `X-RVM-API-KEY`.
- **Logic:** Key di-generate otomatis saat `RvmMachine` dibuat. Digunakan untuk endpoint kritikal seperti `handshake`.

### B. Interface Kiosk (Signed URL)
- **Mechanism:** Laravel Signed Route (HMAC SHA-256).
- **ID:** `uuid` (36-character random string).
- **Logic:** Link ke Kiosk (`/rvm-ui/{uuid}`) hanya valid jika signature sesuai dengan payload URL. Akses dari luar tanpa signature yang sah akan menghasilkan **403 Forbidden**.

### C. Maintenance Access (PIN-based)
- **Mechanism:** Short-lived PIN (6 digits).
- **Table:** `technician_assignments`.
- **Logic:** Teknisi harus memasukkan PIN yang valid dan belum kadaluarsa (`pin_expires_at`). Sistem dilengkapi **Rate Limiting** (max 5 attempts/hour) untuk mencegah brute force.

### D. Admin & Dashboard (Session-based)
- **Mechanism:** Laravel Sanctum / Web Session.
- **Role:** RBAC (Super Admin, Admin, Operator, Teknisi).

## 2. Security Configuration (Infrastructure)

- **Trust Proxies:** Diaktifkan untuk Cloudflare (`bootstrap/app.php`). Mendukung header `X-Forwarded-Proto` untuk validasi signature HTTPS yang akurat.
- **HTTPS Enforcement:** Dipaksa melalui `AppServiceProvider` berdasarkan `APP_URL`.
- **FastCGI Hardening:** Nginx dikonfigurasi (`docker/nginx/conf.d/app.conf`) untuk mengirimkan parameter `HTTPS on` ke PHP-FPM.

## 3. Data Protection
- **Opaque IDs:** Penggunaan **UUID** di layer publik untuk menyembunyikan jumlah total mesin atau urutan ID di database.
- **Hidden Secret:** API Key dan Pin disembunyikan dari representasi JSON Model secara default.

---
📝 **Artifact Saved:** `Docs/Artifacts/MyRVM-Server/Architecture/Security-Design-20260126.md`
