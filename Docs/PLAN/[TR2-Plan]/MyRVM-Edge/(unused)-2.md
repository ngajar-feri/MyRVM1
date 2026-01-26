Tentu, ini adalah pembaruan dokumen **Project Plan: MyRVM-Edge** ke **Versi 1.8**.

Revisi ini mencakup detail teknis terbaru mengenai:
1.  Struktur **JSON Payload Handshake** yang lengkap (termasuk Health Metrics & Timezone).
2.  Logika **Setup Wizard** yang lebih canggih (Kalibrasi Sensor & Smart Clean).
3.  Penempatan **API Key** yang benar (Header).

---

# Project Plan: MyRVM-Edge (Edge Control Service)

**Version:** 1.8
**Target Platform:** NVIDIA Jetson Orin Nano (JetPack 6.x)
**Role:** Hardware Bridge, AI Inferencing Node, & WebSocket Client.

---

## 1. Executive Summary
**MyRVM-Edge** adalah Service Daemon yang berjalan di perangkat keras RVM. Tugas utamanya adalah menjembatani dunia fisik dengan MyRVM-Server. Aplikasi ini mengelola kontrol perangkat keras, AI lokal, manajemen pembaruan model, dan inisialisasi sistem (provisioning).

Aplikasi ini tidak merender UI utama secara lokal, melainkan meluncurkan browser dalam **Mode Kiosk** yang menampilkan antarmuka web dari Server. Namun, untuk **Instalasi Awal (Day-0)**, aplikasi ini menyediakan antarmuka lokal (*Setup Wizard*) untuk konfigurasi dan kalibrasi.

---

## 2. Technical Architecture

### **Core Technology**
*   **Language:** Python 3.10+ (AsyncIO).
*   **Architecture Pattern:** Event-Driven Architecture (WebSocket Consumers).

### **Libraries & Dependencies**
1.  **Communication:** `websockets`/`python-socketio`, `requests`.
2.  **Hardware Control:** `gpiod`, `pyserial`.
3.  **Computer Vision (AI):** `ultralytics` (YOLOv11/12), `torch` (PyTorch), `opencv-python` (cv2).
4.  **System Monitor:** `psutil` (CPU, RAM, Disk, Temp).
5.  **Setup Wizard:** `fastapi`/`flask` (Web Server Lokal), `python-multipart`.

---

## 3. System Design & Data Flow

### **A. Startup Logic (Smart Boot)**
Script `main.py` mengecek keberadaan `secrets.env`.
*   **Unprovisioned:** Jalankan **Setup Wizard** di `http://127.0.0.1:8080`.
*   **Provisioned:** Lakukan **Handshake** ke Server -> Jika sukses, Launch Kiosk UI (URL Server).

### **B. Setup Wizard Workflow (Day-0 Installation)**

#### **Langkah 1: Import Kredensial**
*   **Metode:** USB Flashdisk (File Explorer) atau Reverse QR Code (Upload via HP).
*   **Input:** File `rvm-credentials.json` (berisi Serial Number & API Key).

#### **Langkah 2: Auto-Discovery & Health Check**
*   Sistem mendeteksi hardware (Kamera, Mikrokontroler).
*   **Health Check:** Mengambil snapshot CPU, RAM, Suhu, dan Disk.
*   **Smart Clean Trigger:** Jika Disk Usage > 90%, munculkan popup peringatan dengan tombol **[Bersihkan Cache]**.
    *   *Action:* Menghapus log lama dan temp images, menjaga file config dan DB tetap aman.

#### **Langkah 3: Kalibrasi Sensor (Hybrid)**
*   Membaca nilai sensor saat ini.
*   Memungkinkan teknisi mengoreksi nilai referensi (Misal: Mengatur `max_distance_cm` untuk sensor kapasitas bin saat tong kosong).

#### **Langkah 4: Finalisasi (Handshake)**
*   Mengirim JSON Payload lengkap ke Server.
*   Jika Server merespons 200 OK, simpan config, matikan Wizard, dan Reboot ke Mode Operasional.

---

## 4. Feature Specifications

### **4.1. System Health Monitor**
Menggunakan `psutil` dan pembacaan file sistem termal.
*   **Metrics:** CPU Load (%), Memory Usage (%), Disk Usage (%), CPU Temperature (°C).
*   **Usage:** Dikirim saat Handshake dan Heartbeat rutin.

### **4.2. Provisioning & Calibration Manager**
*   Menangani logika Setup Wizard.
*   Menyimpan hasil kalibrasi sensor ke dalam struktur JSON config sebelum dikirim ke server.

### **4.3. AI Pipeline Engine**
*   **Acceptance Logic:**
    *   ✅ **ACCEPTED:** Class `mineral` AND `not_empty` NOT detected.
    *   ❌ **REJECTED:** Class `soda`, `milk`, `yogurt`, `dishwasher`, `non_mineral` OR (`mineral` AND `not_empty` detected).

### **4.4. Auto Update System**
*   Update `best.pt` dari GitHub Releases dengan verifikasi **SHA256**.

---

## 5. API Integration & Endpoints (MyRVM-Server)

### **5.1. Authentication & Config (Handshake)**
*   **Endpoint:** `POST /api/v1/edge/handshake`
*   **Headers:** `X-RVM-API-KEY: [API_KEY_DARI_JSON]`
*   **Payload (JSON):**
    ```json
    {
      "hardware_id": "RVM-202601-006",
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

      "config": {
        "cameras": [
          { "id": 0, "path": "/dev/video0", "model": "IMX219", "status": "ready" }
        ],
        "sensors": [
          { 
            "name": "bin_capacity", 
            "type": "Ultrasonic HC-SR04", 
            "status": "calibrated",
            "max_distance_cm": 120 
          }
        ],
        "actuators": [ ... ],
        "microcontroller": { "port": "/dev/ttyTHS1", "status": "connected" }
      },

      "diagnostics": {
        "network_check": "pass",
        "ai_inference_test": "pass"
      }
    }
    ```

### **5.2. Operational Endpoints**
*   `POST /api/v1/edge/heartbeat`: Kirim Health Metrics & IP (Interval 60s).
*   `POST /api/v1/edge/telemetry`: Kirim data sensor real-time.
*   `POST /api/v1/edge/deposit`: Kirim hasil transaksi & gambar (multipart).

---

## 6. Security & Data Protection Measures

### **6.1. Credential Security**
*   API Key tidak pernah dikirim dalam Body JSON, melainkan via **Header HTTP**.
*   File `rvm-credentials.json` dihapus dari disk segera setelah berhasil diimpor ke `.env`.

### **6.2. Local Data Security**
*   **Smart Clean:** Mekanisme otomatis untuk menghapus log tua dan file temporary jika disk penuh, tanpa menyentuh file sistem krusial.
*   **Image Handling:** Gambar hanya diupload ke Server (yang meneruskan ke MinIO), tidak disimpan permanen di Edge.

---

## 7. Directory Structure

```text
MyRVM-Edge/
├── config/
│   ├── settings.py
│   ├── secrets.env       # Generated by Wizard (Contains API Key)
│   └── config.json       # Generated by Handshake response (UI URL, Tokens)
├── src/
│   ├── setup_wizard/     # [MODULE] Local Installation UI
│   │   ├── app.py        # FastAPI Server
│   │   ├── static/       # HTML/JS/CSS for Wizard
│   ├── core/
│   │   ├── provisioning.py # JSON Import Logic
│   │   ├── diagnostics.py  # Health Check, psutil, Smart Clean
│   │   ├── hardware_map.py # Auto-discovery logic
│   ├── ai/
│   │   ├── engine.py
│   │   └── updater.py
│   ├── drivers/
│   ├── network/
│   │   ├── api_client.py # Handshake & Deposit Logic
│   │   └── ws_client.py
│   └── main.py           # Boot Logic
├── data/
│   ├── local.db
│   └── temp_images/      # Buffer folder (Cleaned by Smart Clean)
└── requirements.txt      # + psutil, fastapi, uvicorn
```