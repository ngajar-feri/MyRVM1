# Edge API v2.0 Specification
**Date:** 2026-01-27
**Status:** Implemented (v2.0)
**Context:** Communication protocol between RVM-Edge (Jetson/IoT) and RVM-Server.

## Authentication
All endpoints require the `X-RVM-API-KEY` header.
- **Header:** `X-RVM-API-KEY: <api_key_hash>`
- **Source:** `rvm-credentials.json` on the Edge device.

---

## 1. Handshake (System Context)
**Endpoint:** `POST /api/v1/edge/handshake`
**Purpose:** Sync identity, hardware config, and receive operational policy.

### Request Body
```json
{
  "hardware_id": "RVM-2026-001",
  "name": "RVM Lobby Utama",
  "ip_local": "192.168.1.100",
  "ip_vpn": "100.x.x.x",
  "config": {
    "cameras": [{"path": "/dev/video0", "model": "IMX219"}],
    "sensors": [{"type": "ultrasonic", "port": "GPIO18"}]
  },
  "health_metrics": { ... }
}
```

### Response (200 OK)
```json
{
  "status": "success",
  "data": {
    "identity": { "rvm_id": 1, "rvm_uuid": "..." },
    "kiosk": { "url": "https://.../rvm-ui/{uuid}?signature=..." },
    "websocket": { "channel": "rvm.{uuid}", "auth_token": "..." },
    "policy": { "is_maintenance_mode": false, "threshold": 90 }
  }
}
```

---

## 2. Heartbeat (Alive Check)
**Endpoint:** `POST /api/v1/edge/devices/{id}/heartbeat`
**Purpose:** Signal online status and update dynamic IPs.

---

## 3. Deposit (Single Item)
**Endpoint:** `POST /api/v1/edge/deposit`
**Content-Type:** `multipart/form-data`
**Purpose:** Submit a single processed item with its image.

### Request Body
- `image`: (Binary) JPEG/PNG file.
- `status`: "ACCEPTED" | "REJECTED"
- `data`: (JSON String)
  ```json
  {
    "classification": "plastic_bottle",
    "confidence": 0.98,
    "brand": "Aqua",
    "weight_g": 15.2,
    "session_id": "user-session-12345"
  }
  ```
- `session_id`: (String, Optional) Transaction Session ID.

### Response (201 Created)
```json
{
  "status": "success",
  "message": "Deposit processed",
  "data": {
    "image_url": "https://.../deposits/.../image.jpg",
    "ai_result": { ... }
  }
}
```

---

## 4. Sync Offline (Bulk)
**Endpoint:** `POST /api/v1/edge/sync-offline`
**Content-Type:** `application/json`
**Purpose:** Upload transactions stored locally during offline mode.

### Request Body
```json
{
  "transactions": [
    {
      "session_id": "local-uuid-1",
      "timestamp": "2026-01-27T10:00:00Z",
      "items": [
        { "type": "plastic", "weight": 10, "points": 50 },
        { "type": "aluminum", "weight": 5, "points": 100 }
      ]
    }
  ]
}
```

### Response (200 OK)
```json
{
  "status": "success",
  "message": "Synced 1 offline transactions",
  "synced_count": 1
}
```
