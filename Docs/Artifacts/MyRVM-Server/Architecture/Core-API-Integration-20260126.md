# Artifact: MyRVM-Server Core API & Integration
**Date:** 2026-01-26
**Revision Sequence:** 1
**Reference Change:** Initial Documentation
**Revised From:** 
**Context:** Dokumentasi Integrasi Perangkat & Antarmuka

---

## 1. Edge Device Integration (IoT API)

Endpoint ini digunakan oleh RVM-Edge untuk berkomunikasi dengan Server.

| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/edge/handshake` | API Key | Inisialisasi koneksi, sinkronisasi config, dan generate Signed URL Kiosk. |
| `POST` | `/api/v1/edge/heartbeat` | API Key | Laporan status mesin (online/offline) secara berkala. |
| `POST` | `/api/v1/edge/telemetry` | API Key | Kirim data sensor (kapasitas, suhu, dll). |
| `GET` | `/api/v1/edge/model-sync` | API Key | Cek ketersediaan Versi Model AI terbaru dari CV Server. |

## 2. Kiosk UI Endpoints (Machine Interface)

Endpoint ini digunakan oleh Browser di mesin RVM (Chromium) untuk menjalankan UI.

| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/rvm-ui/{uuid}` | Signed URL | Render Main UI Kiosk Dashboard. |
| `POST` | `/api/v1/kiosk/auth/pin` | Machine UUID | Verifikasi PIN Teknisi untuk masuk Mode Maintenance. |
| `POST` | `/api/v1/kiosk/maintenance/command` | PIN Session | Kirim perintah hardware (misal: Buka pintu, reboot). |
| `GET` | `/api/v1/kiosk/session/token` | Machine UUID | Generate QR Code token untuk User Login via Mobile Apps. |

## 3. WebSocket Channels (Real-time)

| Channel | Event | Description |
| :--- | :--- | :--- |
| `rvm.{uuid}` | `HardwareCommandEvent` | Kirim perintah dari Dashboard Admin ke Edge secara real-time. |
| `rvm.{uuid}` | `TelemetryUpdateUpdate` | Update status sensor di Dashboard Dashboard secara live. |

## 4. Integration Flow (Handshake)
1.  **Edge** mengirim `hardware_id` + `ip_address` via API Key.
2.  **Server** validasi API Key, temukan mesin, update status ke 'online'.
3.  **Server** mengembalikan `uuid`, `kiosk_url` (signed), dan `ws_auth_token`.
4.  **Edge** membuka `kiosk_url` di browser lokal.

---
📝 **Artifact Saved:** `Docs/Artifacts/MyRVM-Server/Architecture/Core-API-Integration-20260126.md`
