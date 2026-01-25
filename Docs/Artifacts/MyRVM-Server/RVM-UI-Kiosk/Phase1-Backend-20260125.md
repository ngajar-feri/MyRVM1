# Artifact: RVM-UI Kiosk - Phase 1 Backend Implementation
**Date:** 2026-01-25
**Revision Sequence:** 1
**Reference Change:** Initial Implementation
**Revised From:** -
**Context:** Implementasi Phase 1 dari RVM-UI Kiosk Interface Module

---

## Summary

Phase 1 mencakup pembuatan seluruh backend foundation untuk modul RVM-UI Kiosk, termasuk controllers, routes, middleware, dan event classes untuk WebSocket broadcasting.

---

## Output Files Created

### Controllers

| File | Path | Fungsi |
|------|------|--------|
| `KioskController.php` | `app/Http/Controllers/Dashboard/` | Web controller untuk render halaman kiosk, validasi machine UUID |
| `SessionController.php` | `app/Http/Controllers/Api/Kiosk/` | Generate token sesi QR, aktivasi mode tamu |
| `AuthController.php` | `app/Http/Controllers/Api/Kiosk/` | Validasi PIN teknisi dengan rate limiting |
| `MaintenanceController.php` | `app/Http/Controllers/Api/Kiosk/` | Kirim command hardware, cek status mesin |
| `LogController.php` | `app/Http/Controllers/Api/Kiosk/` | Log viewer (scoped by machine_id) |
| `ConfigController.php` | `app/Http/Controllers/Api/Kiosk/` | Konfigurasi tema (Light/Dark/Auto) |

### Middleware

| File | Path | Fungsi |
|------|------|--------|
| `ValidateKioskMachine.php` | `app/Http/Middleware/` | Validasi header `X-Machine-UUID` untuk API requests |

### Event Classes (WebSocket Broadcasting)

| File | Path | Broadcast Channel |
|------|------|-------------------|
| `HardwareCommandEvent.php` | `app/Events/Kiosk/` | `rvm.{machineUuid}` |
| `UiUpdateEvent.php` | `app/Events/Kiosk/` | `rvm.{machineUuid}` |
| `SessionAuthorizedEvent.php` | `app/Events/Kiosk/` | `rvm.{machineUuid}` |
| `ItemProcessedEvent.php` | `app/Events/Kiosk/` | `rvm.{machineUuid}` |

---

## Routes Configuration

### Web Route (Added to `routes/web.php`)

```php
Route::get('/rvm-ui/{machine_uuid}', [KioskController::class, 'index'])
    ->name('kiosk.index');
```

### API Routes (Added to `routes/api.php`)

**Prefix:** `/api/v1/kiosk`  
**Middleware:** `throttle:60,1`

| Method | Endpoint | Controller Method |
|--------|----------|-------------------|
| GET | `/session/token` | `SessionController@getToken` |
| POST | `/session/guest` | `SessionController@activateGuest` |
| POST | `/auth/pin` | `AuthController@verifyPin` |
| POST | `/maintenance/command` | `MaintenanceController@sendCommand` |
| GET | `/maintenance/status` | `MaintenanceController@getStatus` |
| GET | `/logs` | `LogController@index` |
| GET | `/config` | `ConfigController@getConfig` |
| POST | `/config/theme` | `ConfigController@updateTheme` |

---

## Key Logic Implemented

### 1. Session Token Generation
- Token menggunakan SHA256 hash
- Expiry 5 menit (auto-refresh)
- Disimpan di Cache dengan prefix `kiosk_session:`
- QR Content format: `myrvm://session/{token}?m={machineUuid}`

### 2. PIN Authentication Security
- Rate limiting: 5 attempts per hour per machine
- PIN di-hash dengan bcrypt
- Clear rate limiter on success
- Session expires after 2 hours

### 3. Log Isolation
```php
// Logs ALWAYS scoped to current machine
$query = SystemLog::where('rvm_machine_id', $machine->id)
```

### 4. Maintenance Commands
- Allowed commands whitelist
- Elevated permissions for dangerous commands (`reboot_edge`)
- WebSocket broadcasting ke Edge device

---

## Security Checklist ✅

- [x] SQL Injection: Using Eloquent ORM
- [x] Rate Limiting: PIN verification throttled
- [x] Machine Validation: Middleware validates UUID
- [x] Log Isolation: Scoped queries
- [x] Input Validation: All endpoints use Laravel validation

---

## Revision History Log

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 2026-01-25 | Initial Phase 1 implementation | AI Assistant |

---

📝 **Artifact Saved:** `Docs/Artifacts/MyRVM-Server/RVM-UI-Kiosk/Phase1-Backend-20260125.md`
