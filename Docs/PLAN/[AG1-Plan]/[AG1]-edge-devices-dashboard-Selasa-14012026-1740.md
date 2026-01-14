# Edge Devices Dashboard - Implementation Plan

**Versi Dokumen:** 1.0  
**Tanggal Revisi:** Selasa-14 Januari 2026 - 05:40 PM  
**Tujuan:** Upgrade halaman Edge Devices dengan form registrasi lengkap sesuai spesifikasi  
**Status:** Belum

---

## Current State vs Requirements

| Feature | Current | Required |
|---------|---------|----------|
| Form Fields | 3 (Serial, RVM, IP) | 10+ fields in 3 sections |
| Map Widget | ❌ Missing | ✅ Leaflet/OSM integration |
| API Key Gen | ❌ Missing | ✅ Show once + copy + download |
| Hardware Config | ❌ Missing | ✅ Controller type, Camera, Threshold |
| maintenance_sessions | ❌ Missing | ✅ PIN hashing w/ expiry |
| API Status | ❌ 500 Error | ✅ Working endpoints |

---

## Proposed Changes

### Database Migrations

#### [NEW] create_maintenance_sessions_table.php
```php
Schema::create('maintenance_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rvm_machine_id')->constrained()->onDelete('cascade');
    $table->foreignId('technician_id')->constrained('users');
    $table->string('pin_hash');
    $table->timestamp('expires_at');
    $table->timestamp('used_at')->nullable();
    $table->timestamps();
});
```

#### [MODIFY] edge_devices table
Add columns:
- `api_key` (hashed, one-time display)
- `location_name`, `inventory_code`, `description`
- `latitude`, `longitude`, `address`
- `controller_type`, `camera_id`, `threshold_full`, `ai_model`

---

### Backend Changes

#### [MODIFY] EdgeDeviceController.php
- Fix `index()` API 500 error (use correct model)
- Update `register()` with validation, UUID, API key generation
- Add `generateApiKey()` helper
- Add `downloadConfig()` for JSON config export

#### [NEW] MaintenanceSessionController.php
- `generatePin()` - Create maintenance PIN
- `validatePin()` - Verify PIN from Edge device

---

### Frontend Changes

#### [MODIFY] index-content.blade.php

**Section 1: Identity & Status**
- Location Name (required)
- Inventory Code
- Initial Status (Maintenance/Inactive)
- Description
- ReGenerate API Key button

**Section 2: Geolocation (Peta Interaktif)**
- Leaflet Map Widget (Open Street Map)
- Latitude (readonly, auto from pin)
- Longitude (readonly, auto from pin)
- Address (auto-fill from reverse geocoding)

**Section 3: Hardware Config**
- Controller Type dropdown (NVIDIA Jetson, RaspberryPI, ESP32, etc.)
- Camera ID
- Threshold Full (%, default 90)
- AI Model dropdown

**Success Modal**
- Display API Key once
- Copy button with toast feedback
- Download Config JSON button

#### [MODIFY] devices.js
- Add map widget initialization
- Handle form submission with API key display
- Implement copy-to-clipboard
- Add config download functionality

---

## API Endpoints

| Method | Endpoint | Action |
|--------|----------|--------|
| GET | `/api/v1/edge/devices` | List all devices |
| POST | `/api/v1/edge/devices/register` | Register device |
| GET | `/api/v1/edge/devices/{id}/config` | Download config |
| POST | `/api/v1/maintenance/generate-pin` | Generate PIN |
| POST | `/api/v1/edge/maintenance/validate` | Validate PIN |

---

## Verification Plan

### Automated Tests
```bash
docker compose exec app php artisan test --filter=EdgeDevice
```

### Manual Verification
1. Open http://localhost:8000/dashboard/devices
2. Click "Register Device"
3. Fill 3-section form
4. Verify map pin updates lat/long
5. Submit and verify API key modal
6. Copy API key and download config
7. Verify device appears in list

---

## Rollback Plan

```bash
# Rollback migration
docker compose exec app php artisan migrate:rollback --step=2

# Revert files
git checkout -- app/Http/Controllers/Api/EdgeDeviceController.php
git checkout -- resources/views/dashboard/devices/index-content.blade.php
git checkout -- public/js/modules/devices.js

# Clear cache
docker compose exec app php artisan optimize:clear
```

---

## Implementation Order

1. ⬜ Fix API 500 error (model mismatch)
2. ⬜ Create maintenance_sessions migration
3. ⬜ Update edge_devices schema
4. ⬜ Update EdgeDevice model
5. ⬜ Redesign registration form (3 sections)
6. ⬜ Add Leaflet map widget
7. ⬜ Implement API key generation
8. ⬜ Create success modal with copy/download
9. ⬜ Add MaintenanceSession controller
10. ⬜ Update API documentation (Swagger)
