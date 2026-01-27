```json
{
  // --- 1. IDENTITAS (Wajib) ---
  "hardware_id": "JETSON-ORIN-SN-14239482", // Dari device_id
  "name": "RVM KU1",  // Dari rvm-credentials.json

  // --- 2. JARINGAN & LOKASI (Auto Detect) ---
  "ip_local": "192.168.1.105",
  "ip_vpn": "100.85.120.45",
  "timezone": "Asia/Jakarta",

  // --- 3. INFO PERANGKAT LUNAK (Auto Detect) ---
  "system": {
      "jetpack_version": "6.x", // Dari versi Jetpack
      "firmware_version": "v2.1.0-beta", // Dari versi Python
      "python_version": "3.10.12", // Dari versi Python
      "ai_models": {
        "model_name": "best.pt",
        "model_version": "v1.0.0-beta", // dari config.json
        "hash": "a1b2c3d4...", // dari config.json
        "last_update": "2026-01-17T00:00:00Z" // dari config.json
      }
    },

  // --- 4. INFO PERANGKAT FISIK (Auto Detect) ---
  "hardware_info": { // Sesuaikan dengan nama field di tabel edge_devices pada database 
    "microcontroller": {
      "type": "ESP32", // ESP32, Arduino, Raspberry Pi Pico, dll.
      "connection": "UART", // UART, I2C, SPI, USB, dll.
      "port": "/dev/ttyTHS1", // /dev/ttyTHS1, /dev/ttyUSB0, dll.
      "baud_rate": 115200, // 115200, 9600, dll.
      "status": "connected"
    },
    "cameras": [
      {
        "id": 0,                                // Device ID (Short Path)
        "path": "/dev/video0",                  // Device ID Lengkap
        "name": "Logitech C920 HD Pro",         // Informasi Utama: Friendly Name
        "connection_type": "USB",               // Informasi Sekunder: Interface
        "physical_location": "usb-1.4:1.0",     // Informasi Sekunder: Lokasi Fisik (Bus)
        "serial_number": "SN: 8F21...9A",       // Informasi Sekunder: Serial Number unik
        "status": "ready",                      // Informasi Utama: Status Koneksi (ready/busy/error)
        "capabilities": {                       // Informasi Teknis (Dropdown/Tooltip)
           "max_resolution": "1920x1080",
           "format": "MJPG",
           "fps": 30
        },
        "role": "object_detection"              // Fungsi dalam sistem RVM
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
      } // dan Sensor-sensor lainnya yang akan ditambahkan.
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
        "last_reading": "green", // green = Ready, red = Error, blue = Processing
        "pin": 10
      } // dan Actuator-actuator lainnya yang akan ditambahkan.
    ]
  },

  // --- 6. HASIL DIAGNOSTIK (Untuk Log Audit Server) ---
  "diagnostics": {
        "network_check": "pass",
        "camera_check": "pass",
        "motor_test": "pass",
        "ai_inference_test": "pass" // Tambahan: memastikan model AI bisa load
  }
}