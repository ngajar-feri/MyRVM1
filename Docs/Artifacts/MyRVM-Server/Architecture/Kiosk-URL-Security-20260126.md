# Artifact: Kiosk URL Security Implementation
**Date:** 2026-01-26
**Revision Sequence:** 1
**Reference Change:** Implementasi Signed URL dengan UUID
**Revised From:** -
**Context:** Mengamankan akses ke antarmuka Kiosk menggunakan kombinasi UUID dan Signed URL

---

## Summary

Sistem keamanan URL Kiosk diimplementasikan untuk memastikan bahwa hanya mesin RVM yang valid (yang telah melakukan handshake) yang dapat mengakses antarmuka layar sentuh.

## Key Logic

### UUID vs Serial Number
- **UUID**: `550e8400-e29b-41d4-a716-446655440000` (36-char, opaque, immutable)
- **Serial Number**: `RVM-202601-006` (human-readable, internal tracking)
- UUID digunakan untuk external security, Serial Number untuk internal references

### Signed URL Format
```
https://server/rvm-ui/{UUID}?signature={SHA256_HASH}
```

### Middleware Flow
1. Edge melakukan handshake → Server generate signed URL via `URL::signedRoute()`
2. Edge membuka URL di Chromium
3. Middleware `signed` memvalidasi signature
4. Jika valid → render Kiosk UI
5. Jika invalid → 403 Forbidden

## Files Created/Modified

### Created
- `database/migrations/2026_01_26_104100_add_uuid_to_rvm_machines.php`

### Modified
- `app/Models/RvmMachine.php` - Added uuid column
- `routes/web.php` - Added signed middleware
- `app/Http/Controllers/Api/EdgeDeviceController.php` - URL::signedRoute()
- `app/Http/Controllers/Dashboard/KioskController.php` - Lookup by uuid
- `app/Http/Controllers/Api/Kiosk/AuthController.php` - Fixed access_pin
- `storage/api-docs/api-docs.json` - Updated examples

## Revision History Log

| Ver | Date | Changes | Author |
|:----|:-----|:--------|:-------|
| 1.0 | 2026-01-26 | Initial implementation with UUID signed URL | AI Assistant |
