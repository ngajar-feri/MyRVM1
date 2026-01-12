Versi Dokumen: 1.0
Tanggal Revisi: Kamis-09 Januari 2026 - 07:30 PM
Tujuan: Mendokumentasikan rencana pengembangan dan integrasi komponen RVM-Edge (Jetson Orin Nano) dengan RVM-Server untuk edge computing, AI processing, dan hardware control pada mesin Reverse Vending Machine.
Status: Belum

# Rencana Integrasi RVM-Edge dengan RVM-Server

## 1. Pendahuluan

Dokumen ini merinci rencana pengembangan komponen **RVM-Edge**, yaitu platform edge computing berbasis Jetson Orin Nano yang akan menjadi "otak" dari setiap unit mesin RVM fisik. RVM-Edge bertanggung jawab untuk:
- Kontrol hardware (motor, LED, sensor, camera)
- AI processing lokal (YOLO11 + SAM2)
- Komunikasi dengan RVM-Server via Tailscale VPN
- User interface melalui LCD Touchscreen
- Sinkronisasi AI model dan telemetri real-time

## 2. Arsitektur Sistem

### 2.1. Komponen RVM-Edge
```
┌─────────────────────────────────────────────────┐
│           RVM-Edge (Jetson Orin Nano)          │
│                                                 │
│  ┌──────────────┐      ┌──────────────────┐   │
│  │ Flask API    │◄────►│  Hardware Layer  │   │
│  │ (Gunicorn)   │      │  - Motor DC      │   │
│  └──────┬───────┘      │  - LED RGB       │   │
│         │              │  - DHT Sensor    │   │
│  ┌──────▼───────┐      │  - Ultrasonic    │   │
│  │ AI Engine    │      │  - Camera        │   │
│  │ YOLO11+SAM2  │      └──────────────────┘   │
│  └──────────────┘                              │
│         │                                       │
│  ┌──────▼───────┐      ┌──────────────────┐   │
│  │ LCD Touch UI │      │ Tailscale VPN    │   │
│  │ (Kivy/Qt)    │      │ WebSocket Client │   │
│  └──────────────┘      └──────────────────┘   │
└─────────────────────────────────────────────────┘
           │
           │ HTTPS + WebSocket
           ▼
    ┌─────────────────┐
    │  RVM-Server     │
    │  (vm100)        │
    └─────────────────┘
```

### 2.2. Komunikasi dengan Komponen Lain
- **RVM-Server (vm100)**: API calls untuk transaksi, autentikasi, sinkronisasi model, upload gambar & AI results
- **RVM-User Apps**: Melalui RVM-Server sebagai intermediary

**Catatan**: RVM-Edge **TIDAK** berkomunikasi langsung dengan RVM-CV. Semua AI processing dilakukan lokal di Jetson. Jika RVM-Server perlu fraud detection, Server yang akan trigger RVM-CV untuk memproses gambar dari MinIO.

## 3. Spesifikasi Teknis

### 3.1. Platform & Dependencies
- **Hardware**: NVIDIA Jetson Orin Nano (8GB RAM)
- **OS**: JetPack 6.0 (Ubuntu 22.04)
- **Python**: 3.10+
- **Framework Web**: Flask 3.x + Gunicorn
- **AI**: PyTorch 2.x, YOLO11, SAM2
- **UI**: Kivy atau Qt for Python (LCD Touch 7")
- **GPIO**: Jetson.GPIO library
- **Network**: Tailscale VPN client

### 3.2. Hardware Pinout (GPIO)
| Komponen          | Pin GPIO | Tipe       | Keterangan                    |
|-------------------|----------|------------|-------------------------------|
| Motor DC          | GPIO 18  | PWM Output | Kontrol buka/tutup alas (TIDAK DIGUNAKAN di shopping cart mode) |
| LED RGB (R/G/B)   | GPIO 12/13/14 | PWM Output | Session status indicator (8 states) |
| DHT22 Sensor      | GPIO 4   | Digital I/O| Suhu & kelembaban             |
| Ultrasonic Trig   | GPIO 23  | Digital Out| Deteksi penuh bak             |
| Ultrasonic Echo   | GPIO 24  | Digital In | Deteksi penuh bak             |
| Camera            | CSI Port | Video Input| Capture botol untuk AI        |

### 3.3. AI Processing Pipeline
**RVM-Edge melakukan 100% AI processing secara lokal (mandiri)**:
- Camera capture → YOLO11 detection → SAM2 segmentation
- Inference menggunakan `best.pt` yang sudah disinkronkan dari RVM-Server
- Latency target: <2 detik per botol
- **Hasil detection** (gambar + metadata) dikirim ke RVM-Server
- **Tidak ada remote inference ke RVM-CV** - semua compute di Jetson

> **Catatan Penting**: RVM-Edge **TIDAK** melakukan remote inference ke RVM-CV. Jika ada kebutuhan fraud detection atau re-analysis, RVM-Server yang akan trigger RVM-CV untuk memproses gambar yang sudah tersimpan di MinIO.


### 3.4. LCD Touchscreen UI Flow (Shopping Cart Pattern)

```
[IDLE State]
- Screen: Logo + "Tap untuk Memulai" 
- LED: OFF atau breathing putih pelan
         ↓ (User tap)
         
[QR Scanner State]
- Screen: "Scan QR Code dari Aplikasi Anda"
- LED: Putih solid
- Timeout: 30 detik → Back to IDLE
         ↓ (QR valid)
         
[SESSION ACTIVE State] ← Main shopping state
- Screen: "Selamat Datang, [User Name]!"
          "Masukkan botol ke lubang input"
          "Total: 0 item | Rp 0"
          [Tombol: Selesai] [Tombol: Batal]
- LED: BIRU BLINKING (indikasi session aktif)
- Session Timer: Mulai countdown (max 5 menit)
- **TIDAK ADA PINTU** - Input langsung seperti shopping cart

    ↓ Loop: User masukkan botol
    
    [Item Processing]
    - Screen: "Menganalisis..." + loading animation
    - LED: Tetap BIRU BLINKING
    - AI: YOLO11 + SAM2 (1-2 detik)
    
         ↓ (AI selesai)
    
    [Auto Decision - ACCEPTED]
    - Screen: "✓ Botol PET - +Rp 500" (hijau, 1 detik)
              "Total: 1 item | Rp 500"
    - LED: Flash HIJAU 2x (0.5 detik), lalu kembali BIRU BLINKING
    - **TIDAK ada motor** - Botol langsung masuk ke bak
    - Update running total di screen
    - Send item data ke Server (background)
    - Return ke "Masukkan botol..." screen
    
    atau
    
    [Auto Decision - REJECTED]
    - Screen: "✗ Tidak dapat diproses" (merah, 1 detik)
              "Total tetap: 0 item | Rp 0"
    - LED: Flash MERAH 2x (0.5 detik), lalu kembali BIRU BLINKING  
    - Botol tidak masuk ke bak (user ambil kembali)
    - Return ke "Masukkan botol..." screen
    
    ↓ (User tekan "Selesai" ATAU Timeout)
    
[COMMIT Transaction]
- Screen: "Memproses transaksi..."
- LED: BIRU solid (tidak blink)
- POST /api/v1/transactions/commit ke Server
         ↓ (Server response)
         
[Transaction Complete]
- Screen: "Terima Kasih!"
          "Total: 3 item diterima"
          "Poin: +150"
          "Saldo Anda: 1,650 poin"
- LED: HIJAU solid (3 detik)
- WebSocket: Send notification ke RVM-User App
         ↓ (Jeda 5 detik)
         
[Cleanup & Return]
- Screen fade out
- LED: OFF
- Clear session data
- Return ke IDLE State
```

**Session Ending Scenarios:**

**1. Normal Finish (User tekan "Selesai")**
```
- User tekan tombol "Selesai" di touchscreen
- Commit transaction ke Server
- Show summary → Jeda 5 detik
- Return to IDLE
```

**2. Cancel (User tekan "Batal")**
```
- User tekan tombol "Batal"
- Confirm dialog: "Batalkan transaksi? [Ya] [Tidak]"
- If Ya:
  - POST /api/v1/transactions/cancel ke Server
  - LED: MERAH solid (2 detik)
  - Screen: "Transaksi dibatalkan"
  - Jeda 3 detik→ Return to IDLE
```

**3. Timeout (Idle dalam session)**
```
- Session countdown: 5 menit dari QR scan
- Saat tersisa 30 detik:
  - Screen overlay: "Session berakhir dalam 30 detik"
  - Countdown timer visible
  - LED: BIRU blink lebih cepat
- Saat timeout (0 detik):
  - Auto-commit transaction (jika ada items)
  - Atau cancel (jika 0 items)
  - LED: KUNING solid (2 detik)
  - Screen: "Session berakhir"
  - Jeda 3 detik → Return to IDLE
```

**LED Color Specifications:**
- **OFF**: Idle/tidak ada session
- **Putih**: QR scanning
- **Biru Blinking**: Session active (shopping mode)
- **Hijau Flash 2x**: Item accepted
- **Merah Flash 2x**: Item rejected
- **Hijau Solid**: Transaction complete success
- **Merah Solid**: Transaction cancelled
- **Kuning Solid**: Session timeout
- **Putih Breathing**: Standby (opsional)

**Catatan Penting**:
- ✅ **Tanpa Pintu/Motor**: Input langsung seperti shopping cart
- ✅ **Multiple Items**: User bisa masukkan banyak botol
- ✅ **Running Total**: Display update real-time
- ✅ **Manual Commit**: User kontrol kapan selesai
- ✅ **Auto-decision**: Tetap pakai confidence threshold 75%
- ✅ **Session Management**: Timeout prevent abandoned sessions

## 4. API Endpoints RVM-Edge

### 4.1. Endpoints yang Disediakan oleh RVM-Edge (Flask API)

| Method | Endpoint                  | Deskripsi                                      |
|--------|---------------------------|------------------------------------------------|
| `GET`  | `/api/edge/health`        | Health check status mesin                      |
| `GET`  | `/api/edge/telemetry`     | Data sensor real-time (suhu, ultrasonic, dll)  |
| `POST` | `/api/edge/command`       | Remote command dari Server (reboot, test LED)  |
| `POST` | `/api/edge/update-model`  | Trigger download model AI terbaru              |
| `GET`  | `/api/edge/hardware-test` | Test semua hardware (motor, LED, sensor)       |

### 4.2. Endpoints yang Dipanggil ke RVM-Server

| Method | Endpoint (Server)                     | Deskripsi                                |
|--------|---------------------------------------|------------------------------------------|
| `POST` | `/api/v1/edge/register`               | Register RVM-Edge saat boot              |
| `POST` | `/api/v1/edge/heartbeat`              | Kirim heartbeat + telemetri setiap 30s   |
| `POST` | `/api/v1/transactions/start`          | Mulai sesi transaksi (QR code)           |
| `POST` | `/api/v1/transactions/item`           | Submit hasil AI detection per botol      |
| `POST` | `/api/v1/transactions/commit`         | Finalisasi transaksi                     |
| `GET`  | `/api/v1/edge/model-sync`             | Cek versi model AI terbaru               |
| `GET`  | `/api/v1/edge/download-model/{hash}`  | Download best.pt dari MinIO              |
| `POST` | `/api/v1/edge/telemetry`              | Kirim log sensor (fleksibel JSON array)  |
| `POST` | `/api/v1/edge/upload-image`           | Upload gambar ke MinIO                   |
| `POST` | `/api/v1/edge/update-location`        | Update location (manual atau GPS module) |

### 4.3. Payload Specification untuk Transaction Item

**Request ke RVM-Server: POST /api/v1/transactions/item**
```json
{
  "session_id": "uuid-from-qr-code",
  "edge_device_id": "jetson-serial-12345",
  "item_data": {
    // Status Decision
    "status": "accepted",  // "accepted" atau "rejected"
    "confidence": 0.87,
    "confidence_threshold": 0.75,
    
    // AI Detection Results
    "detected_class": "PET_bottle",
    "bounding_box": [120, 80, 340, 520],
    "segmentation_mask_url": "minio://masks/item_001.png",
    
    // Image Data - DUAL UPLOAD
    "images": {
      // 1. Gambar Original (Raw Camera Capture)
      "original_url": "minio://images/raw/20260109/session_123/item_001_original.jpg",
      "original_size_kb": 1024,
      "original_resolution": "1920x1080",
      
      // 2. Gambar Terproses (Dengan Anotasi YOLO11 + SAM2)
      "processed_url": "minio://images/processed/20260109/session_123/item_001_annotated.jpg",
      "processed_size_kb": 856,
      "processed_resolution": "1920x1080",
      
      // Metadata
      "segmentation_mask_url": "minio://masks/20260109/session_123/item_001_mask.png",
      "captured_at": "2026-01-09T20:05:30+07:00",
      "upload_complete": true
    },
    
    // Model Information
    "model_version": "yolo11_v3_best.pt",
    "model_hash": "sha256:abc123...",
    "sam2_version": "sam2_b.pt",
    
    // Physical Measurements (Rekomendasi untuk masa depan)
    "estimated_weight_grams": 35,  // Dari ultrasonic atau sensor berat
    "estimated_volume_ml": 600,    // Dari bounding box dimensions
    "material_type": "PET",
    "color_detected": "transparent",
    
    // Quality Metrics (Rekomendasi untuk masa depan)
    "is_damaged": false,
    "is_crushed": false,
    "label_readable": true,
    "contamination_level": "clean",  // "clean", "slight", "moderate", "heavy"
    
    // Environmental Data (Rekomendasi untuk masa depan)
    "temperature_celsius": 28.5,
    "humidity_percent": 65,
    "processing_time_ms": 1850
  },
  "timestamp": "2026-01-09T20:05:32+07:00"
}
```

**Response dari RVM-Server:**
```json
{
  "status": "success",
  "item_id": 12345,
  "transaction_id": 6789,
  
  // Reward Calculation
  "points_earned": 50,
  "monetary_value": 500,  // Rp 500 (representasi value, bukan uang cash)
  "currency": "IDR",
  
  // Points System Clarification:
  // - points_earned: Angka poin yang didapat user
  // - Points bisa ditukar dengan:
  //   1. Voucher tenant (marketplace, F&B, dll)
  //   2. Emas digital (FUTURE: via stable coin GOLD cryptocurrency)
  // - monetary_value: Hanya untuk display/informasi, bukan cash withdrawal
  
  // Transaction Summary (untuk display di LCD)
  "session_summary": {
    "total_items": 3,
    "total_accepted": 2,
    "total_rejected": 1,
    "total_points": 100,
    "total_value": 1000,  // Rp 1000
    "session_status": "active"  // "active", "completed"
  },
  
  // User Information (untuk display)
  "user": {
    "name": "John Doe",
    "current_balance": 1500,  // Total points setelah transaksi ini
    "tier": "silver"  // "bronze", "silver", "gold" (Rekomendasi gamification)
  },
  
  // Fraud Detection Flags (Rekomendasi untuk masa depan)
  "fraud_check": {
    "requires_review": false,
    "confidence_mismatch": false,
    "duplicate_item": false,
    "suspicious_pattern": false
  },
  
  // Promo & Incentives (Rekomendasi untuk masa depan)
  "bonus": {
    "available": true,
    "type": "double_points",
    "message": "Bonus 2x points untuk PET hari ini!",
    "points_multiplier": 2.0
  },
  
  "message": "Item berhasil diproses",
  "timestamp": "2026-01-09T20:05:33+07:00"
}
```

**Catatan Penting tentang Dual Image Upload**:

1. **Gambar Original (Raw)**:
   - Resolusi penuh dari camera (1920x1080)
   - Tidak ada modifikasi/anotasi apapun
   - Format: JPEG dengan quality 95%
   - **Tujuan**: Fraud detection, audit trail, re-analysis oleh RVM-CV
   - Path: `minio://images/raw/{date}/{session_id}/item_{id}_original.jpg`

2. **Gambar Terproses (Annotated)**:
   - Hasil YOLO11 detection (bounding boxes) + SAM2 segmentation overlay
   - Visual proof dari AI decision untuk user/admin
   - Format: JPEG dengan quality 85% (sudah ada overlay)
   - **Tujuan**: User feedback, admin review, debugging model
   - Path: `minio://images/processed/{date}/{session_id}/item_{id}_annotated.jpg`

3. **Segmentation Mask**:
   - SAM2 mask dalam format PNG (binary mask)
   - Black & white image showing segmented object
   - **Tujuan**: Precise volume calculation, quality analysis
   - Path: `minio://masks/{date}/{session_id}/item_{id}_mask.png`

**Upload Workflow di RVM-Edge**:
```
1. Camera capture → Save original_image
2. AI Processing: YOLO11 → SAM2
3. Generate annotated_image (draw boxes + mask overlay)
4. Generate mask_image (SAM2 output)
5. Upload 3 files ke MinIO via RVM-Server API
6. Submit transaction/item dengan URLs ke 3 files
```

**Storage Estimates**:
- Original: ~1 MB per item (1920x1080, JPEG Q95)
- Processed: ~800 KB per item (dengan overlay, JPEG Q85)
- Mask: ~50 KB per item (PNG binary, 1-bit per pixel)
- **Total per item**: ~1.85 MB
- **Estimasi usage**:
  - 50 transaksi/hari × 3 items avg = 150 items
  - 150 × 1.85 MB = **278 MB/day**
  - **8.3 GB/month** per RVM device
  - 10 RVM devices = **83 GB/month**

**Retention Policy (Rekomendasi)**:
- Original images: 6 bulan (untuk fraud/audit)
- Processed images: 3 bulan (untuk review)
- Masks: 1 bulan (bisa di-generate ulang if needed)

## 5. Database Schema Changes (RVM-Server)

### 5.1. Tabel Baru: `edge_devices`
```sql
CREATE TABLE edge_devices (
    id BIGSERIAL PRIMARY KEY,
    rvm_id BIGINT REFERENCES reverse_vending_machines(id),
    device_serial VARCHAR(255) UNIQUE NOT NULL,
    tailscale_ip INET,
    last_heartbeat TIMESTAMP,
    ai_model_version VARCHAR(50),
    status VARCHAR(20) DEFAULT 'offline',
    hardware_info JSONB,
    
    -- Location Tracking (Manual or GPS Module)
    latitude DECIMAL(10, 8),  -- e.g., -6.20876543
    longitude DECIMAL(11, 8),  -- e.g., 106.84567890
    location_accuracy_meters DECIMAL(6, 2),  -- GPS accuracy (null if manual)
    location_source VARCHAR(20) DEFAULT 'manual',  -- 'manual' or 'gps_module'
    location_last_updated TIMESTAMP,
    location_address TEXT,  -- Human-readable address from reverse geocoding
    
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Catatan:
-- 1. RVM (Reverse Vending Machine) = Edge Device = Jetson Orin Nano + komponen
-- 2. reverse_vending_machines table = Alat fisik keseluruhan
-- 3. edge_devices table = Jetson device yang ada di dalam RVM
-- 4. Location: Saat ini manual via Maps, future: GPS module auto-update
```

### 5.2. Tabel Baru: `edge_telemetry`
```sql
CREATE TABLE edge_telemetry (
    id BIGSERIAL PRIMARY KEY,
    edge_device_id BIGINT REFERENCES edge_devices(id),
    sensor_data JSONB, -- Fleksibel untuk DHT, ultrasonic, dll
    cpu_temp DECIMAL(5,2),
    gpu_temp DECIMAL(5,2),
    disk_usage DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 5.3. Location Tracking Workflow

**Fase 1: Manual Location Input (Current Implementation)**

```
Installation Flow:
1. Admin/Super Admin create installation task di dashboard
   → Pilih RVM device
   → Set target location di Maps (Google/Bing/HERE/Mapbox/OSM)
   → Assign ke Teknisi
   
2. Teknisi menerima task → Pergi ke lokasi

3. Saat instalasi, Teknisi buka calibration page di RVM-Edge:
   → Maps interface untuk konfirmasi lokasi akurat
   → Teknisi adjust pin di peta jika perlu
   → Click "Save Location"
   
4. RVM-Edge send location ke RVM-Server:
   POST /api/v1/edge/update-location
   {
     "edge_device_id": "jetson-serial-12345",
     "latitude": -6.2087654,
     "longitude": 106.8456789,
     "location_source": "manual",
     "accuracy_meters": null,
     "address": "Jl. Sudirman No. 123, Jakarta Pusat",
     "updated_by_user_id": 456  // Teknisi ID
   }
   
5. Server save ke edge_devices.latitude & longitude
6. Location tersimpan di local file/memory RVM-Edge
   Path: /opt/myrvm-edge/config/location.json
   
7. Jika reinstall atau pindah lokasi:
   → File location.json di-reset atau diupdate
   → Teknisi ulang proses calibration #3
```

**Fase 2: GPS Module Auto-Update (Future)**

```
1. GPS Module terintegrasi ke Jetson GPIO
2. Cronjob di RVM-Edge (setiap 1 jam atau bersama telemetry):
   → Read GPS coordinates
   → If location changed > 50 meters:
     → Send update ke Server
     → Update local location.json
   
3. Auto update payload:
   POST /api/v1/edge/update-location
   {
     "edge_device_id": "jetson-serial-12345",
     "latitude": -6.2087890,
     "longitude": 106.8457123,
     "location_source": "gps_module",
     "accuracy_meters": 5.2,  // From GPS
     "updated_by_user_id": null  // Auto
   }
   
4. Server reverse geocoding untuk dapat address
5. Alert admin jika location berubah signifikan (possible theft)
```

**Storage Location Decision**: ✅ `edge_devices` table
- Location adalah property semi-static dari RVM
- Tidak berubah sesering sensor telemetry
- Efficient untuk query device info
- Bisa tambah `rvm_location_history` table untuk audit trail

### 5.4. Update Tabel: `reverse_vending_machines`
Tambahkan kolom:
```sql
ALTER TABLE reverse_vending_machines
ADD COLUMN last_maintenance TIMESTAMP,
ADD COLUMN last_model_sync TIMESTAMP;
```

## 6. Security & Authentication

### 6.1. API Key untuk Edge Device
- Setiap RVM-Edge memiliki unique API Key yang disimpan di environment variable
- Format: `Authorization: Bearer <edge_api_key>`
- Key di-generate saat first setup via RVM-Server admin panel

### 6.2. Tailscale VPN
- Semua komunikasi Edge ↔ Server melalui Tailscale network
- IP Address: `100.117.234.2` (RVM-Edge) → `100.123.143.87` (RVM-Server)
- Fallback: HTTPS dengan SSL pinning jika VPN down

### 6.3. Model AI Integrity
- Hash verification untuk setiap `best.pt` download
- Server menyimpan SHA256 hash, Edge verify sebelum load model

## 7. Struktur Project RVM-Edge

```
MyRVM-Edge/
├── app/
│   ├── __init__.py
│   ├── api/
│   │   ├── health.py          # Health check endpoint
│   │   ├── telemetry.py       # Sensor data endpoint
│   │   └── commands.py        # Remote command handler
│   ├── ai/
│   │   ├── yolo_detector.py   # YOLO11 inference
│   │   ├── sam2_segmenter.py  # SAM2 segmentation
│   │   └── model_manager.py   # Model sync & update
│   ├── hardware/
│   │   ├── motor.py           # Motor DC control
│   │   ├── led.py             # LED RGB control
│   │   ├── sensors.py         # DHT + Ultrasonic
│   │   └── camera.py          # CSI Camera capture
│   ├── ui/
│   │   ├── main_screen.py     # Kivy/Qt main UI
│   │   ├── qr_scanner.py      # QR code scanner
│   │   └── transaction_ui.py  # Transaction flow UI
│   └── services/
│       ├── server_client.py   # RVM-Server API client
│       ├── websocket_client.py# WebSocket connection
│       └── telemetry_service.py # Auto telemetry sender
├── models/                    # AI model storage
│   └── best.pt                # Downloaded from Server
├── config/
│   ├── settings.py            # Configuration
│   └── .env                   # API keys, Server URL
├── requirements.txt
├── main.py                    # Flask app entry point
└── README.md
```

## 8. Rencana Pengujian (Staging/Testing)

### 8.1. Unit Testing
- Test setiap hardware component secara terpisah:
  - Motor buka/tutup 10x cycle
  - LED RGB semua warna
  - DHT sensor read accuracy
  - Ultrasonic distance measurement
  - Camera capture quality

### 8.2. AI Pipeline Testing
- Test local inference:
  - Load `best.pt` model
  - Inference 10 sample images (botol PET, HDPE, Kaca)
  - Verify confidence > 0.85
  - Verify detection results accuracy
- Test image upload ke Server:
  - Capture image → Upload ke RVM-Server (MinIO)
  - Verify upload success < 2s

### 8.3. Integration Testing (dengan RVM-Server)
- **Skenario 1: Full Transaction Flow**
  1. Boot RVM-Edge → Auto register ke Server
  2. User scan QR code
  3. Insert botol → AI process → Submit ke Server
  4. Commit transaction → Verify poin bertambah di database
  
- **Skenario 2: Model Sync**
  1. Server admin upload `best.pt` baru
  2. Edge device poll `/api/v1/edge/model-sync`
  3. Detect new version → Download → Verify hash → Load model
  
- **Skenario 3: Remote Control**
  1. Admin kirim command "test-hardware" dari dashboard
  2. Edge execute test LED + Motor
  3. Return result ke Server

### 8.4. Network Resilience Testing
- Test offline mode: Disconnect Tailscale → Verify cache berfungsi
- Test WebSocket reconnection
- Test API retry mechanism (3 retry dengan exponential backoff)

## 9. Deployment Plan

### 9.1. First Time Setup
1. Flash JetPack 6.0 ke Jetson Orin Nano
2. Install Tailscale client: `curl -fsSL https://tailscale.com/install.sh | sh`
3. Join Tailscale network dengan auth key
4. Clone repository: `git clone <repo-url> /opt/myrvm-edge`
5. Install dependencies: `pip install -r requirements.txt`
6. Setup systemd service untuk auto-start:
   ```bash
   sudo cp myrvm-edge.service /etc/systemd/system/
   sudo systemctl enable myrvm-edge
   sudo systemctl start myrvm-edge
   ```

### 9.2. Update Workflow
- **Over-The-Air (OTA) Update**:
  1. Push update ke GitHub repository
  2. Admin trigger "Update Edge" dari RVM-Server dashboard
  3. Server send command ke Edge → Edge pull latest code → Restart service
  
- **Manual Update** (jika OTA gagal):
  1. SSH ke Jetson via Tailscale
  2. `cd /opt/myrvm-edge && git pull`
  3. `sudo systemctl restart myrvm-edge`

## 10. Changelog

| Tanggal            | Perubahan                                          | Author |
|--------------------|----------------------------------------------------|--------|
| 09-01-2026 19:30   | Pembuatan dokumen rencana integrasi RVM-Edge       | AG1    |

## 11. Rollback Plan

### Level 1: Application Error (AI/API Bug)
- **Gejala**: AI inference error, API crash, UI freeze
- **Action**:
  1. Rollback ke commit sebelumnya: `git checkout <stable_commit>`
  2. Restart service: `sudo systemctl restart myrvm-edge`
- **Estimated Downtime**: 2-5 menit per unit

### Level 2: Hardware Control Error
- **Gejala**: Motor tidak respon, LED mati, sensor error
- **Action**:
  1. Fallback ke manual mode (disable GPIO control)
  2. Notifikasi teknisi via RVM-Server alert system
  3. Technician dispatch untuk physical inspection
- **Estimated Downtime**: 30-60 menit per unit

### Level 3: Network/Tailscale Issue
- **Gejala**: Tidak bisa connect ke RVM-Server
- **Action**:
  1. RVM-Edge masuk **offline mode**:
     - Tetap accept transaksi menggunakan cache QR validation
     - Store transaksi di local SQLite
     - Sync ke Server saat network kembali
  2. Admin cek Tailscale status di vm101 (Net Host)
- **Estimated Downtime**: 0 menit (graceful degradation)

### Level 4: Critical System Failure (Jetson Crash)
- **Action**:
  1. Auto-reboot via watchdog timer
  2. Jika gagal reboot 3x → Status "maintenance" di Server
  3. Technician dispatch dengan spare Jetson unit
- **Estimated Downtime**: 2-4 jam per unit

## 12. Timeline Estimasi

| Fase                          | Durasi   | Deliverable                          |
|-------------------------------|----------|--------------------------------------|
| Setup environment & hardware  | 3 hari   | Jetson configured, GPIO tested       |
| Flask API development         | 5 hari   | REST API endpoints ready             |
| AI engine integration         | 7 hari   | YOLO11+SAM2 inference working        |
| Hardware control layer        | 4 hari   | Motor, LED, sensor control ready     |
| LCD Touch UI development      | 6 hari   | Transaction flow UI complete         |
| Server integration & testing  | 5 hari   | Full integration test passed         |
| **Total**                     | **30 hari** | Production-ready RVM-Edge         |

## 13. Catatan Tambahan

- **Logging**: Semua log disimpan lokal di `/var/log/myrvm-edge/` dan dikirim ke RVM-Server setiap 5 menit
- **Watchdog**: Implementasi watchdog timer untuk auto-recovery jika aplikasi freeze
- **Camera Resolution**: 1920x1080 untuk AI processing, resize ke 640x640 untuk YOLO11 input
- **Power Management**: Implement sleep mode jika idle > 10 menit (matikan camera, dim LCD)

---

**Dokumen ini akan di-update seiring dengan progress pengembangan.**
