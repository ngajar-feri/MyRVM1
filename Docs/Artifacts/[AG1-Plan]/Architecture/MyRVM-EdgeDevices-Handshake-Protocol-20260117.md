# Artifact: Edge Device Handshake Protocol
**Date:** 2026-01-17
**Revision Sequence:** -
**Reference Change:** -
**Context:** Standardizing the Heartbeat/Handshake payload for Auto-Discovery of hardware info (Cameras, Sensors, etc.).

## 1. Summary
This document defines the JSON payload structure for the `POST /api/v1/devices/{id}/heartbeat` endpoint. This handshake mechanism allows Edge Devices to "report" their current hardware configuration (microcontroller, cameras, sensors, actuators) to the Server, enabling **Auto-Discovery** and automatic database updates, specifically for `camera_id` and `hardware_info`.

## 2. Key Decisions / Logic
- **Endpoint:** `POST /api/v1/devices/{id}/heartbeat`
- **Auto-Discovery:** Server updates `hardware_info` JSON column and extracting critical fields like `camera_id` from the payload.
- **Source of Truth:** The Edge Device (Jetson/RPi) knows its attached hardware better than the Server.
- **Camera ID:** Automatically extracted from `hardware_info.cameras[0].path` (e.g., `/dev/video0`) to populated the `camera_id` column.

## 3. The Output (JSON Payload Specification)

```json
{
  "hardware_id": "JETSON-ORIN-SN-14239482",
  "ip_local": "192.168.1.105",
  "ip_vpn": "100.85.120.45",
  "firmware_version": "v2.1.0-beta",
  "hardware_info": { 
    "microcontroller": {
      "type": "ESP32", 
      "connection": "UART", 
      "port": "/dev/ttyTHS1",
      "baud_rate": 115200 
    },
    "cameras": [
      {
        "id": 0,                                
        "path": "/dev/video0",                  
        "name": "Logitech C920 HD Pro",         
        "connection_type": "USB",               
        "physical_location": "usb-1.4:1.0",     
        "serial_number": "SN: 8F21...9A",       
        "status": "ready",                      
        "capabilities": {                       
           "max_resolution": "1920x1080",
           "format": "MJPG",
           "fps": 30
        },
        "role": "object_detection"              
      }
    ],
    "sensors": [
      {
        "name": "bin_ultrasonic",
        "friendly_name": "Sensor Level Bak Penampungan",
        "model": "HC-SR04",
        "interface": "GPIO",
        "pins": {
           "trigger": 12,
           "echo": 13
        },
        "status": "online",
        "last_reading": "10",
        "unit": "cm"
      },
      {
        "name": "intake_proximity",
        "friendly_name": "Sensor Deteksi Benda Masuk",
        "model": "IR Obstacle",
        "interface": "GPIO",
        "pin": 18,
        "active_level": "LOW",
        "status": "online",
        "last_reading": "1",
        "unit": "cm"
      },
      {
        "name": "internal_temp",
        "friendly_name": "Sensor Suhu Internal",
        "model": "DHT22",
        "interface": "GPIO",
        "pin": 4,
        "status": "online",
        "last_reading": "25.5",
        "unit": "°C"
      }
    ],
    "actuators": [
      {
        "name": "sorting_motor",
        "friendly_name": "Motor Pemilah",
        "model": "Stepper NEMA17",
        "interface": "GPIO",
        "driver": "TB6600",
        "pins": { "step": 23, "dir": 24, "enable": 25 }
      },
      {
        "name": "door_lock",
        "friendly_name": "Kunci Pintu",
        "model": "Solenoid 12V",
        "interface": "GPIO",
        "pin": 27
      },
      {
        "name": "status_led",
        "friendly_name": "Lampu Indikator",
        "model": "RGB LED Strip",
        "interface": "GPIO",
        "last_reading": "green", 
        "pin": 10
      }
    ],
    "system": {
        "jetpack_version": "6.0",
        "python_version": "3.10.12",
        "ai_model_version": "v1.0.0-beta",
        "ai_models": {
            "model_name": "best.pt",
            "model_version": "v1.0.0-beta",
            "hash": "a1b2c3d4...",
            "last_update": "2026-01-17T00:00:00Z"
        }
    }
  }
}
```

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-17 | - | Initial Create |
