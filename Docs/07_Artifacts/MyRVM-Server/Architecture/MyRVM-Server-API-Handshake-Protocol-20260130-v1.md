# Artifact: MyRVM Handshake API Protocol
**Date:** 2026-01-30 14:00
**Revision Sequence:** 1
**Reference Change:** Corrected JSON Schema key from `config` to `hardware_info`
**Revised From:** [EdgeDeviceController.php](../../../../MyRVM-Server/app/Http/Controllers/Api/EdgeDeviceController.php)
**Context:** Reconciliation of API documentation with actual server validation and Python client implementation.

## 1. Summary
This artifact defines the authoritative protocol for the Edge-to-Server Handshake. Previous documentation incorrectly referenced the hardware configuration payload as `config`, while both the Server Validator and Python Client implemented it as `hardware_info`. This document standardizes the schema to match the working code.

## 2. Key Decisions
- **Schema Alignment:** The JSON key `config` is officially deprecated and replaced by `hardware_info` to match the `EdgeDeviceController` validation rules and `EdgeDiagnostics` output.
- **Single Source of Truth:** This artifact serves as the reference for both Swagger documentation and Edge Client development.

## 3. The Output: Corrected JSON Schema

### Endpoint
`POST /api/v1/edge/handshake`

### Request Payload
```json
{
  "hardware_id": "RVM-202601-006",
  "name": "RVM KU1",
  "ip_local": "192.168.1.105",
  "ip_vpn": "100.80.50.20",
  "timezone": "Asia/Jakarta",
  "firmware_version": "v1.7.0",
  "controller_type": "NVIDIA Jetson Orin Nano",
  "ai_model_version": "YOLO11n-v1.0.0",
  "health_metrics": {
    "cpu_usage_percent": 15.5,
    "memory_usage_percent": 42.0,
    "disk_usage_percent": 12.8,
    "cpu_temperature": 45.0
  },
  "hardware_info": {  // CORRECTED KEY (was 'config')
    "cameras": [
      {
        "id": "cam_0",
        "model": "IMX219",
        "resolution": "3280x2464"
      }
    ],
    "sensors": [
      {
        "type": "ultrasonic",
        "port": "GPIO_18",
        "status": "active"
      }
    ],
    "actuators": [
      {
        "type": "servo_motor",
        "role": "sorter_flap",
        "status": "calibrated"
      }
    ],
    "microcontroller": {
      "model": "ESP32",
      "firmware": "v2.1"
    }
  },
  "diagnostics": {
    "network_check": "pass",
    "camera_check": "pass",
    "motor_test": "pass",
    "ai_inference_test": "pass"
  }
}
```

## 4. Implementation Reference

### Server Side (Laravel)
- **File:** `app/Http/Controllers/Api/EdgeDeviceController.php`
- **Method:** `handshake()`
- **Validator:**
  ```php
  'hardware_info' => 'nullable|array',
  'hardware_info.cameras' => 'nullable|array',
  // ...
  ```

### Client Side (Python)
- **File:** `src/services/api_client.py`
- **Method:** `handshake()`
- **Payload Construction:**
  ```python
  payload = diag.get_specs()
  # diag.get_specs() returns dictionary with key "hardware_info"
  ```

## History & References
| Ver | Date | Changes | Ref (Link to Artifact) |
| :-- | :-- | :------ | :--------------------- |
| 1.0 | 2026-01-30 | Initial Protocol Definition | N/A |
