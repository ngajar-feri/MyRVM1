# Artifact: POST /api/v1/edge/handshake Endpoint
**Date:** 2026-01-26
**Revision Sequence:** 1
**Reference Change:** Initial Implementation
**Revised From:** -
**Context:** RVM-Edge Setup Wizard membutuhkan endpoint untuk sinkronisasi konfigurasi awal saat instalasi mesin.

---

## Summary
Endpoint handshake memungkinkan RVM-Edge mengirimkan identitas dan konfigurasi hardware ke server, dan menerima konfigurasi operasional sebagai respons.

## Key Logic

### Authentication
- Menggunakan header `X-RVM-API-KEY` (bukan Bearer token)
- Middleware `ValidateRvmApiKey` memvalidasi key dari `rvm_machines.api_key`
- Return 401 jika key invalid, 403 jika mesin diblokir

### Payload Request
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `hardware_id` | string | ✅ | ID mesin dari credentials.json |
| `name` | string | ✅ | Nama display mesin |
| `ip_local` | string | - | IP address lokal |
| `ip_vpn` | string | - | Tailscale VPN IP |
| `timezone` | string | - | Timezone (default: Asia/Jakarta) |
| `firmware_version` | string | - | Versi firmware |
| `controller_type` | string | - | Tipe controller (Jetson, dll) |
| `ai_model_version` | string | - | Versi model AI aktif |
| `health_metrics` | object | - | CPU, memory, disk, temperature |
| `config` | object | - | Konfigurasi hardware (cameras, sensors, actuators) |
| `diagnostics` | object | - | Hasil diagnostik terakhir |

### Response Data
| Field | Description |
|-------|-------------|
| `identity` | ID, UUID, dan nama mesin dari database |
| `kiosk.url` | URL signed untuk browser Kiosk Mode |
| `websocket` | Channel dan auth token untuk real-time |
| `policy` | Operational policy (maintenance mode, threshold, dll) |
| `ai_model` | Target version dan download URL model AI |

## Files Created/Modified

### Created
- `app/Http/Middleware/ValidateRvmApiKey.php`
- `database/migrations/2026_01_26_092000_add_hardware_config_to_edge_devices.php`

### Modified
- `app/Models/EdgeDevice.php` - Added new columns
- `app/Http/Controllers/Api/EdgeDeviceController.php` - Added `handshake()` method
- `routes/api.php` - Added route
- `bootstrap/app.php` - Registered middleware alias
- `storage/api-docs/api-docs.json` - Added documentation

## Error Handling

| Code | Condition | Message |
|------|-----------|---------|
| 401 | API key missing/invalid | Kunci API Tidak Valid |
| 403 | Machine blocked/suspended | Mesin ini diblokir oleh Server |
| 422 | Validation error | Data konfigurasi korup |
| 500 | Server error | Server Gangguan |

## Revision History Log

| Ver | Date | Changes | Author |
|:----|:-----|:--------|:-------|
| 1.0 | 2026-01-26 | Initial Implementation | AI Assistant |
