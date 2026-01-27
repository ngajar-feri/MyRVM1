# Project Plan: MyRVM-Edge (Edge Control Service)

#### **Project Name:** MyRVM-Edge
#### **Directory:** MyRVM-Edge
#### **Version:** 2.0
#### **Revised From:** [Modul Edge Control Service-v1.8](Docs/PLAN/MyPlan/MyRVM-Edge/Modul Edge Control Service-v1.8.md)
#### **Reference Change:** v2.0 Menghilangkan ambiguitas antara mode online dan offline. Menyatukan visi Server-Driven UI dengan Edge-Control-Service.
#### **Status:** Production Ready
#### **Target Platform:** NVIDIA Jetson Orin Nano (JetPack 6.x)
#### **Role:** Hardware Bridge, AI Inferencing Node, & WebSocket Client.
#### **Target Server:** MyRVM-Server (https://myrvm.penelitian.my.id/)
#### **Responsibilities:**
*   **Handshake & Authentication:** Registrasi perangkat ke Server, otorisasi WebSocket.
*   **Real-time Control:** Terima perintah dari Server (Buka Pintu, Ambil Foto) dan kirim status ke Server.
*   **Sensor Data Upload:** Terima data dari sensor (pH, Suhu, Kelembaban) dan kirim ke Server.
*   **Deposit Submission:** Terima data pengumpulan sampel dan kirim ke Server.
*   **Model Update Management:** Cek dan unduh pembaruan model AI dari Server.

---

## 1. Executive Summary
**MyRVM-Edge** bukanlah aplikasi web, melainkan sebuah **Master Daemon** (Layanan Latar Belakang / `Service Daemon`) yang berjalan di perangkat keras RVM. Tugas utamanya adalah menjembatani dunia fisik (sensor, motor, kamera) dengan otak pusat di Cloud (MyRVM-Server). Aplikasi ini mengelola logika kontrol perangkat keras, pemrosesan AI lokal (on-device), dan manajemen pembaruan model secara mandiri.

Aplikasi ini tidak merender UI (Antarmuka Pengguna). Sebaliknya, ia meluncurkan browser dalam **Mode Kiosk** yang menampilkan antarmuka web dari Server, termasuk antarmuka Pengguna (User) dan antarmuka Maintenance (Teknisi). Logika kontrol fisik (seperti "Buka Pintu") dipicu melalui jembatan komunikasi real-time (WebSocket) antara UI Server dan Daemon Python ini. Mengelola logika kontrol perangkat keras dan pemrosesan AI secara lokal (*on-device*) untuk performa real-time.

Namun, untuk Instalasi Awal (Day-0), aplikasi ini menyediakan antarmuka lokal sementara (Setup Wizard) untuk konfigurasi kredensial.

MyRVM-Edge v2.0 adalah **Master Daemon** yang mengelola integritas operasional mesin RVM secara mandiri. Menggunakan pola arsitektur **Hybrid-Communication**, perangkat ini bertindak sebagai:
1.  **Client:** Menghubungkan hardware ke Cloud (MyRVM-Server) via WebSocket (Reverb).
2.  **Server:** Menyediakan jembatan lokal (FastAPI WebSocket) untuk UI Kiosk agar tetap berfungsi 100% saat internet terputus (**Offline Guest Mode**).
3.  **Controller:** Menjalankan inferensi AI YOLO11+SAM2, menggunakan `best.pt` models dan logika validasi botol secara lokal.

---

## 2. Technical Architecture & Communication Flow

### **A. Dual-Socket Architecture**
Untuk mendukung **Offline Capability**, daemon Python menjalankan dua tugas konektivitas secara *concurrent* (bersamaan):

| Channel | Protocol | Target | Purpose |
| :--- | :--- | :--- | :--- |
| **Cloud Bridge** | WSS (Client) | Laravel Reverb | Real-time command dari Admin, Sync Transaksi Online. |
| **Local Bridge** | WS (Server) | Localhost:8888 | Menyuplai data ke Vue.js UI (Kiosk) saat Offline. |

### **B. The "Triangle" Data Flow**
1.  **UI (Browser)** ↔ **Edge (Python)**: Status sensor real-time & perintah hardware lokal.
2.  **Edge (Python)** ↔ **Server (Cloud)**: Sinkronisasi data transaksi, telemetri, dan model AI.
3.  **UI (Browser)** ↔ **Server (Cloud)**: Inisialisasi sesi QR dan otentikasi User (hanya saat online).

### **Core Technology**
*   **Language:** Python 3.10+ (AsyncIO).
*   **Architecture Pattern:** Event-Driven Architecture (WebSocket Consumers).

### **Libraries & Dependencies**
1.  **Communication:**
    *   `websockets` atau `python-socketio`: Untuk komunikasi real-time 2 arah dengan Laravel Reverb.
    *   `requests`: Untuk panggilan API HTTP (Handshake, Log Upload, Transaction Submission, Model Update Check).
2.  **Hardware Control:**
    *   `gpiod`: Standar Linux modern untuk kontrol GPIO (Motor, Relay).
    *   `pyserial`: Komunikasi UART dengan mikrokontroler (ESP32) jika diperlukan ekspansi IO.
3.  **Computer Vision (AI):**
    *   `ultralytics` (YOLOv11/12): Deteksi Objek Cepat.
    *   `torch` (PyTorch): Backend untuk SAM2 (Segment Anything Model).
    *   `best.pt`: Model deteksi objek yang dioptimalkan yang akan di gunakan sebagai model deteksi. 
        *   URL File (direct): `https://github.com/ngajar-feri/MyRVM1/releases/download/[releases version]/best.pt` 
        *   URL File (GitHub API): `https://api.github.com/repos/ngajar-feri/MyRVM1/releases/latest`
    *   `opencv-python` (cv2): Akuisisi gambar dari kamera.
4.  **Data & Config:**
    *   `sqlite3`: Penyimpanan lokal untuk cache konfigurasi dan buffer log saat offline.
    *   `python-dotenv`: Manajemen kredensial (API Key).
5.  **Setup Wizard:**
    *   `fastapi` / `flask`: Web server ringan untuk antarmuka instalasi lokal.
    *   `python-multipart`: Untuk menangani upload file JSON.

---

## 3. System Design & Data Flow
### **A. Startup Logic (Smart Boot)**

Script `main.py` akan mengecek keberadaan file konfigurasi yang valid (`secrets.env` atau database lokal yang terisi).

*   **Kondisi Unprovisioned (Belum Dikonfigurasi):**
    *   Sistem mendeteksi belum ada kredensial.
    *   Menjalankan modul **Setup Wizard** (Local Web Server).
    *   Membuka browser lokal ke `http://localhost:8080`.
*   **Kondisi Provisioned (Siap Operasi):**
    *   Sistem mendeteksi kredensial valid.
    *   Melakukan Handshake ke Server.
    *   Menjalankan Operational Loop.
    *   Meluncurkan browser ke URL UI (`http://myrvm.penelitian.my.id/rvm-ui/{uuid}`) dari Server (Mode Kiosk) .

### **B. Setup Wizard Workflow (Day-0 Installation)**

Antarmuka lokal sederhana berbasis web yang tampil di layar RVM untuk teknisi. Tujuannya adalah mengimpor file `rvm-credentials.json` yang berisi API Key dan Serial Number.

#### **Langkah 1: Import Kredensial**

##### **Metode 1: Import via USB (File Explorer)**
1.  Teknisi mencolokkan Flashdisk berisi `rvm-credentials.json`.
2.  Di layar Wizard, Teknisi menekan tombol **"Pilih File Kredensial"**.
3.  Browser membuka jendela **File Explorer** (Dialog native OS).
4.  Teknisi menavigasi ke USB Drive dan memilih file JSON tersebut.
5.  Script memvalidasi isi file dan menyimpannya ke sistem.

##### **Metode 2: Upload via HP (Reverse QR Code)**
*Alternatif jika USB bermasalah.*
1.  Layar RVM menampilkan **QR Code** yang berisi alamat IP lokal Setup Wizard (contoh: `http://192.168.1.50:8080`).
2.  Teknisi memindai QR tersebut menggunakan **Kamera HP** mereka (terhubung ke WiFi yang sama).
3.  HP Teknisi membuka halaman upload sederhana.
4.  Teknisi mengupload file `rvm-credentials.json` dari HP ke RVM via browser HP.

#### **Langkah 2: Auto-Discovery & Health Check**
*   Sistem mendeteksi hardware (Kamera, Mikrokontroler).
*   **Health Check:** Mengambil snapshot CPU, RAM, Suhu, dan Disk.
*   **Smart Clean Trigger:** Jika Disk Usage > 90%, munculkan popup peringatan dengan tombol **[Bersihkan Cache]**.
    *   *Action:* Menghapus log lama dan temp images, menjaga file config dan DB tetap aman.

#### **Langkah 3: Kalibrasi Sensor (Hybrid)**
*   Membaca nilai sensor saat ini.
*   Memungkinkan teknisi mengoreksi nilai referensi (Misal: Mengatur `max_distance_cm` untuk sensor kapasitas bin saat tong kosong).

#### **Langkah 4: Finalisasi (Handshake)**
1.  **Validasi:** Script mengecek struktur JSON, untuk kebutuhan body request Handshake.
2.  **Hardware Self-Test:** RVM melakukan cek cepat sensor dan motor.
3.  **Mengirim:** JSON Payload lengkap ke Server (Edge memanggil `POST /api/v1/edge/handshake` membawa hardware metadata & health metrics).
4.  **Response Server:** Memberikan `Signed URL Kiosk`, `WebSocket Auth Token`, dan `Target Model Version`.
5.  **Save & Reboot:** **Jika Server merespons 200 OK** simpan config (Kredensial disimpan di `secrets.env`), matikan Wizard, dan Service restart. Masuk ke Mode Operasional.
6.  **Service Initialization:**
    *   Inisialisasi `Local WebSocket Server` (Port 8888).
    *   Inisialisasi `Cloud WebSocket Client` (Laravel Echo/Reverb).
    *   Launch Chromium Browser: `chromium-browser --kiosk [Signed_URL_Kiosk]`.

### **C. Startup Sequence (Normal Mode)**
1.  **Booting:** Script Python berjalan otomatis via `systemd`.
2.  **Hardware Check:** Script memindai `/dev/video*`, `lsusb`, dan GPIO untuk mendeteksi hardware yang terpasang.
3.  **API Handshake:** Script mengirim JSON spesifikasi hardware ke `MyRVM-Server/api/v1/edge/handshake`.
    *   *Tujuannya:* Melaporkan status hidup, update IP terbaru, dan mendapatkan konfigurasi/token terbaru. Pada langkah 4. **Model Update Check:**, fungsi `check_for_update(current_version)` dipanggil setelah Handshake berhasil, karena butuh model_version dari server (response handshake) sebagai pembanding, atau `config.json` menyimpan versi lokal. Pastikan alurnya konsisten: Handshake -> Server bilang "Versi terbaru v1.0.0" -> Edge cek lokal -> Jika beda -> Update (Panggil fungsi `check_for_update(current_version)`).
    *   **Endpoint:** `POST /api/v1/edge/handshake`
    *   **Payload:**
        ```json
        {
          "hardware_id": "jetson-orin-nano-123", // Unique Serial from device
          "ip_address": "192.168.1.100",
          "firmware_version": "1.0.0",
          "specs": {
            "camera": "Logitech C920",
            "sensors": ["ultrasonic", "ir"],
            "motors": ["stepper", "servo"]
          }
        }
        ```
    *   **Response (Server):**
        ```json
        {
          "rvm_id": "RVM-001", // ID Logis dari database Server
          "session_token": "ey...xyz", // Token untuk WebSocket auth
          "ui_url": "https://myrvm.penelitian.my.id/rvm-ui/RVM-001", // URL untuk Kiosk
          "policy": {
            "thresholds": { 
                "mineral": 0.90 // Policy bisa di set di server (MyRVM-Server Dashboard Admin), default 0.90
            },
            "maintenance_mode": false
          },
          "model_version": "v1.0.0"
        }
        ```
    *   **Error Handling:**
        *   `403 Forbidden`: Hardware ID belum didaftarkan Admin. -> *Script masuk mode "Unregistered" (LED Merah berkedip).*
        *   `Connection Error`: -> *Retry setiap 5 detik.*

4.  **Model Update Check:**
    *   **OTA Update:** Jika `local_version != target_version`, download `best.pt` dari GitHub Release, verifikasi SHA256, lalu hot-swap model.
    ```python
    def check_for_update(current_version):
        # URL API yang Anda berikan
        api_url = "https://api.github.com/repos/ngajar-feri/MyRVM1/releases/latest"
        
        try:
            response = requests.get(api_url)
            response.raise_for_status()
            data = response.json()
            
            # 1. Ambil Tag Name dari JSON
            latest_version = data["tag_name"] # nilainya "v1.0.0"
            
            print(f"Versi Lokal: {current_version} | Versi Terbaru: {latest_version}")

            # 2. Bandingkan Versi
            if latest_version != current_version:
                print("Update ditemukan! Mencari asset...")
                
                # 3. Cari asset 'best.pt' dalam array 'assets'
                download_url = None
                for asset in data["assets"]:
                    if asset["name"] == "best.pt":
                        download_url = asset["browser_download_url"]
                        break
                
                if download_url:
                    print(f"Download URL ditemukan: {download_url}")
                    # Panggil fungsi download_model(download_url, ...) di sini
                    # download_model(download_url, ...)
                else:
                    print("Error: Asset 'best.pt' tidak ditemukan dalam rilis ini.")
            else:
                print("Sistem sudah yang terbaru.")

        except Exception as e:
            print(f"Gagal cek update: {e}")

    # Contoh pemakaian
    # check_for_update("v0.0.1")
    ```
5.  **Service Initialization:**
    *   Setelah Handshake sukses, script menyimpan `ui_url` dan `session_token` ke `config.json`.
    *   Inisialisasi `Local WebSocket Server` (Port 8888).
    *   Inisialisasi `Cloud WebSocket Client` (Laravel Echo/Reverb).
    *   Launch Chromium Browser: `chromium-browser --kiosk [Signed_URL_Kiosk]` (Script membuka Chromium Browser `--kiosk` menuju `ui_url` yang diterima dari server).

### **D. Operational Loop (Event Driven)**
*   **Idle:** Menunggu pesan WebSocket dari Server.
*   **Trigger (Server -> Edge):**
    *   Event: `session_authorized` -> Action: Buka Pintu (Motor ON).
    *   Event: `maintenance_command` -> Action: Jalankan perintah maintenance (misal: Test Motor).
*   **Trigger (Sensor -> Edge -> Server):**
    *   Event: Sensor mendeteksi objek -> Action: Trigger Kamera & AI.
    *   Process: YOLO detect + SAM segmentasi (dengan `best.pt` model) -> **Validation Logic**.
    *   Result: Kirim JSON hasil analisis ke Server via HTTP/WS.
*   **Transaction Flow:**
    *   Event: Sensor mendeteksi objek -> Action: Trigger Kamera.
    *   Event: AI Inference (`best.pt` model) -> Action: Dapatkan Class & Tags.
    *   **Validation Logic:** Process: YOLO detect + SAM segmentasi (Lihat bagian AI Pipeline). Terapkan logika `ACCEPTED` / `REJECTED` lokal.
    *   **Action:**
        *   Jika `ACCEPTED`: Kirim JSON hasil analisis status (Accepted/Rejected) serta class dan confidence ke Server untuk logging via HTTP/WS -> Trigger Mekanik -> Kirim sinyal GPIO ke Motor Stepper Penjatuh / Penyortir (Sortir ke Bin).
        *   Jika `REJECTED`: Kirim JSON hasil analisis status (Accepted/Rejected) serta class dan confidence ke Server untuk logging via HTTP/WS -> Trigger Mekanik LED menyala Merah dan Muncul Pesan `Botol Di Tolak mohon Ambil dan Periksa kembali (Kembalikan/Tolak).
    *   **Submission:** Kirim JSON ke API `/api/v1/edge/deposit`.
---

## 4. Feature Specifications
### **4.1. System Health Monitor**
Menggunakan `psutil` dan pembacaan file sistem termal.
*   **Metrics:** CPU Load (%), Memory Usage (%), Disk Usage (%), CPU Temperature (°C).
*   **Usage:** Dikirim saat Handshake dan Heartbeat rutin.

### **4.2. Provisioning Manager**
Modul ini bertugas menangani input file JSON saat instalasi awal.
*   **Input:** File `rvm-credentials-RVM-202601-006.json`. pembatasan hanya file dengan format `.json`.
    ```json
    {
      "serial_number": "RVM-202601-006",
      "api_key": "WgfeDtPjN79Ip1lOjOimqu0ymLdsI7s39tDymnQtQGVjJnKLf9j4dSlAG8PQs3AO",
      "name": "RVM KU1",
      "generated_at": "2026-01-27T05:17:38.837Z"
    }
    ```
*   **Web Server:** FastAPI/Flask lokal yang hanya aktif saat mode setup.

### **4.3. AI Pipeline Engine, Validation, Image Handling, & Acceptance Logic (Business Rule)**

### **A. Inference Pipeline**
1.  **Capture:** Ambil frame dari CSI Camera.
2.  **YOLO11:** Deteksi Bounding Box & Class.
3.  **SAM2:** Deteksi Bounding Box & Class.
4.  **Model Loader:** Memuat model `best.pt` dari disk.
5.  **Inference:** Menerima frame gambar, menjalankan deteksi objek.
6.  **Validation Logic:**
    *   **ACCEPTED:** Class `mineral` (PET) terdeteksi DAN `not_empty` TIDAK terdeteksi.
    *   **REJECTED:** Terdeteksi `soda`, `milk`, `yogurt`, `dishwasher`, `non_mineral`.
    *   **REJECTED:** Terdeteksi `mineral` tapi `not_empty` juga terdeteksi (ada cairan).

### **B. Image Handling Strategi**
*   **Accepted:** Gambar dikirim langsung ke Server (MinIO) via API Deposit. Hapus lokal segera.
*   **Rejected:** Simpan lokal selama 24 jam untuk audit teknisi, upload ke Server saat *low-traffic* (tengah malam).

### **C. Model Loader & Inference**
*   **Model Loader:** Memuat model `best.pt` dari disk.
*   **Inference:** Menerima frame gambar, menjalankan deteksi objek.

### **D. Acceptance Logic (Business Rule)**
Script Python `ai_engine.py` menerapkan logika validasi berikut terhadap hasil deteksi YOLO:
    *   ✅ **ACCEPTED:**
        *   Class **MUST** be `mineral` (Botol PET).
        *   Class `not_empty` **MUST NOT** be detected (Botol harus kosong).
        *   Class `mineral` AND `not_empty` NOT detected (Botol PET namun ada sesuatu di dalamnya).
    *   ❌ **REJECTED:**
        *   Detected as `soda`, `milk`, `yogurt`, `dishwasher`, or `non_mineral` (Salah Jenis Material).
        *   Detected as `mineral` **BUT** `not_empty` is also detected (Masih ada sisa cairan).
        *   Class `soda`, `milk`, `yogurt`, `dishwasher`, `non_mineral` OR (`mineral` AND `not_empty`) detected (Salah Jenis Material atau Botol PET namun ada sesuatu di dalamnya).

### **4.4. Auto Update System (OTA)**

MyRVM-Edge memiliki kemampuan untuk memperbarui model AI intinya secara otomatis (Over-The-Air) melalui GitHub Releases.

1.  **Check Update:**
    *   Dilakukan saat Startup atau via perintah Admin (WebSocket).
    *   Script membandingkan versi model lokal dengan tag release terbaru di GitHub API: `https://api.github.com/repos/ngajar-feri/MyRVM1/releases/latest`.
2.  **Download Asset:**
    *   Jika versi berbeda, script mem-parsing JSON response untuk mencari asset bernama `best.pt`.
    *   Mengunduh file menggunakan `browser_download_url`.
3.  **Hash Verification (Integrity Check):**
    *   Sebelum mengganti model lama, script menghitung **SHA256 Checksum** dari file yang baru diunduh.
    *   Hash ini dibandingkan dengan nilai yang diharapkan (dikirim dari Server saat Handshake atau diparsing dari deskripsi Release Notes GitHub).
    *   *Tujuannya:* Menghitung SHA256 file yang didownload dan membandingkan dengan checksum yang diharapkan untuk integritas. Mencegah penggunaan file model yang korup atau tidak lengkap.
    *   Verifikasi integritas file yang didownload dari GitHub Release adalah:
        *   Cara Manual (Deskripsi): Parsing Hash dari body (deskripsi release) yang Anda tulis manual. Script bisa mencari string regex SHA256: ([a-f0-9]{64}) di dalam data["body"].
        *   Cara Hash File (Download & Check):  
            * Download file `best.pt` baru ke folder sementara (misal: `/tmp/new_best.pt`).
            * Hitung `SHA256` dari file yang baru didownload tersebut menggunakan Python `hashlib.sha256().hexdigest()`.
            * Bandingkan dengan Hash yang diharapkan (jika disimpan di config server) atau cukup pastikan file tidak korup/kosong.
            * Jika oke, baru timpa (overwrite) file `best.pt` yang lama.
4.  **Hot-Swap:**
    *   Jika Hash valid, model lama dibackup, dan model baru dimuat ke memory tanpa me-restart seluruh sistem (jika memungkinkan) atau melakukan restart service cepat.

### **4.5. Maintenance Control System**
Mekanisme untuk mengontrol perangkat keras secara manual dari UI Kiosk (Mode Maintenance).
*   **UI (Server):** Teknisi menekan tombol di layar sentuh (Web UI Kiosk Mode / MyRVM-Server Kiosk Mode).
*   **Flow:** UI -> Laravel API -> Laravel Reverb (WebSocket) -> **MyRVM-Edge (Python)**.
*   **Action:** Python menerima payload JSON, misal `{"command": "test_motor", "params": {"direction": "cw"}}`, lalu mengeksekusi driver hardware.

---

## 5. API Integration & Endpoints (MyRVM-Server)

RVM-Edge bertindak sebagai klien yang mengkonsumsi API yang disediakan oleh MyRVM-Server. Semua komunikasi dilindungi oleh `X-RVM-API-KEY`.

### **5.1. Authentication & Config**
*   **Method:** `POST`
*   **Endpoint:** `/api/v1/edge/handshake`
*   **Headers:** `X-RVM-API-KEY: [API_KEY_DARI_JSON]`
*   **Payload (JSON):**
    ```json
    {
      // --- 1. IDENTITAS (Wajib) ---
      "hardware_id": "RVM-202601-006",  // Dari rvm-credentials.json
      "name": "RVM KU1",  // Dari rvm-credentials.json

      // --- 2. JARINGAN & LOKASI (Auto Detect) ---
      "ip_local": "192.168.1.105",
      "ip_vpn": "100.80.50.20",
      "timezone": "Asia/Jakarta",
      
      // --- 3. INFO PERANGKAT LUNAK (Auto Detect) ---
      "firmware_version": "v1.7.0",
      "controller_type": "NVIDIA Jetson Orin Nano",
      "ai_model_version": "YOLO11n-v1.0.0",
      
      // --- 4. HEALTH METRICS (Snapshot saat Handshake) ---
      "health_metrics": {
        "cpu_usage_percent": 15.5,
        "memory_usage_percent": 42.0,
        "disk_usage_percent": 12.8,
        "cpu_temperature": 45.0
      },

      // --- 5. KONFIGURASI HARDWARE (Gabungan Auto-Detect + Manual Calibration) ---
      // Masuk ke kolom JSONB 'hardware_config' di Database
      "config": {
        "cameras": [
          { "id": 0, "path": "/dev/video0", "model": "IMX219", "status": "ready" }
        ],
        // Sensor-sensor yang terhubung ke perangkat keras
        "sensors": [
          { 
            "name": "bin_capacity", 
            "type": "Ultrasonic HC-SR04", 
            "status": "calibrated",
            "max_distance_cm": 120 
          },
          {
            "name": "temperature_internal",
            "type": "DHT22",
            "status": "online"
          }
        ],
        // Actuator-actuator yang terhubung ke perangkat keras  
        "actuators": [
          { "name": "door_motor", "status": "ok" },
          { "name": "conveyor_motor", "status": "ok" }
        ],
        // Mikrokontroler yang terhubung ke perangkat keras
        "microcontroller": { "port": "/dev/ttyTHS1", "status": "connected" }
      },

      // --- 6. HASIL DIAGNOSTIK (Untuk Log Audit Server) ---
      "diagnostics": {
        "network_check": "pass",
        "camera_check": "pass",
        "motor_test": "pass",
        "ai_inference_test": "pass" // Tambahan: memastikan model AI bisa load
      }
    }
    ```

*   **Response:** Konfigurasi kebijakan (Thresholds, Maintenance Mode Status).
```json
{
  "status": "success",
  "message": "Handshake successful. Configuration synced.",
  "data": {
    // 1. IDENTITAS LOGIS (Sinkronisasi dengan Database Server)
    "identity": {
      "rvm_id": 105,
      "rvm_uuid": "550e8400-e29b-41d4-a716-446655440000",
      "rvm_name": "RVM KU1"
    },

    // 2. KIOSK UI CONFIGURATION (Untuk Browser)
    "kiosk": {
      // URL spesifik yang wajib dibuka oleh Chromium Browser (Mode Kiosk)
      // INI URL YANG SUDAH DI-SIGN
      "url": "https://myrvm.penelitian.my.id/rvm-ui/550e8400-e29b-41d4-a716-446655440000?signature=7650a2b5e90d7c...",
      
      // Timezone server untuk sinkronisasi jam tampilan dan log
      "timezone": "Asia/Jakarta" 
    },

    // 3. WEBSOCKET CONFIGURATION (Untuk Komunikasi Real-time)
    "websocket": {
      // Channel Privat khusus mesin ini untuk mendengarkan perintah (Buka Pintu, dll)
      "channel": "rvm.550e8400-e29b-41d4-a716-446655440000",
      
      // Token otorisasi sementara agar Python script boleh subscribe ke channel tersebut
      "auth_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIj...",
      
      // Config koneksi Reverb (opsional jika hardcoded di Edge, tapi lebih baik dinamis)
      "host": "myrvm.penelitian.my.id",
      "port": 443,
      "scheme": "wss"
    },

    // 4. OPERATIONAL POLICY (Kebijakan / Aturan Main)
    "policy": {
      // Jika true, mesin menolak transaksi user (hanya teknisi yang bisa akses)
      "is_maintenance_mode": false,
      
      // Batas sensor (Threshold) yang diatur Admin di Dashboard
      "bin_full_threshold_percent": 90,   // Jika sensor baca > 90%, set status FULL
      "camera_idle_timeout_sec": 60,      // Matikan kamera jika idle 60 detik
      "motor_speed_delay": 0.005          // Kecepatan motor stepper
    },

    // 5. AI MODEL VERSIONING (Untuk Auto Update)
    "ai_model": {
      // Versi yang seharusnya berjalan saat ini menurut Server
      "target_version": "v1.0.0",
      
      // Checksum SHA256 untuk verifikasi integritas file
      "hash": "a0e5156e1d53c249d4c859a4545e9505f87d6807c7113376a383da47fa45f241",
      
      // Sumber download (bisa GitHub API atau MinIO URL)
      "update_source_url": "https://api.github.com/repos/ngajar-feri/MyRVM1/releases/latest"
    }
  }
}
```

### **5.2. Health Monitoring**
*   **Method:** `POST`
*   **Endpoint:** `/api/v1/edge/heartbeat`
*   **Frequency:** Setiap 60 detik (Background Task).
*   **Payload:** Status CPU, RAM, Disk Space, dan konektivitas sensor.

### **5.3. Sensor Telemetry**
*   **Method:** `POST`
*   **Endpoint:** `/api/v1/edge/telemetry`
*   **Payload:** Data *time-series* dari sensor (misal: level kepenuhan bin, suhu internal, kelembaban internal, status motor, status camera (busy atau tidak)).

### **5.4. Transaction Submission**
*   **Method:** `POST`
*   **Endpoint:** `/api/v1/edge/deposit`
*   **Payload:** Hasil analisis AI (JSON).
    *   `data`: JSON String berisi hasil analisis.
    ```json
    {
      "session_token": "abc-123",
      "status": "ACCEPTED", // Hasil Validation Logic (ACCEPTED/REJECTED)
      "detected_objects": [
        {"class": "mineral", "conf": 0.98},
        {"class": "not_empty", "conf": 0.05} // Low confidence, ignored
      ],
      "ai_metadata": { "model_hash": "[a-f0-9]{64}", "processing_time_ms": 150 }
    }
    ```
    *   `image`: File gambar (JPG/PNG) dari kamera. **Wajib dikirim ke Server** untuk disimpan ke MinIO oleh Server.

---

## 6. Security & Data Protection Measures

### **6.1. Credential Management**
*   **No Hardcoding:** Kredensial di-inject via file JSON saat instalasi.
*   **Storage:** Disimpan di file `.env` lokal yang dilindungi permission file Linux (`chmod 600`) dan tidak di-track oleh git.
*   **API Key:** API Key tidak pernah dikirim dalam Body JSON, melainkan via **Header HTTP**.
*   **Clean Up:** File `rvm-credentials.json` asli dihapus dari disk segera setelah berhasil diimpor ke `.env`.

### **6.2. Communication Security**
*   **HTTPS/WSS Mandatory:** Semua komunikasi ke Server (HTTP maupun WebSocket) wajib menggunakan enkripsi TLS/SSL.
*   **API Key Header:** Kunci rahasia mesin dikirim melalui Header `X-RVM-API-KEY`, bukan di dalam URL atau Body, untuk mencegah *logging leakage*.
*   **Timeout Handling:** Implementasikan timeout untuk panggilan API (misal: 5 detik). Jika timeout, ulangi request setelah *reconnect*.
*   **Network Error Retry:** Jika terjadi error jaringan (misal: `ConnectionError`), ulangi request setelah *reconnect*.
*   **Server Downtime Handling:** Jika Server putus koneksi, buffer transaksi akan disimpan di database lokal. Setelah koneksi terputus, ulangi pengiriman transaksi setelah *reconnect*.
*   **Transaction Retry:** Setiap kali transaksi dikirim, catat timestamp. Jika terjadi error, ulangi pengiriman setelah *reconnect* dan batas waktu maksimal 5 kali.
*   **Transaction IDempotency:** Setiap transaksi memiliki ID unik. Jika terjadi duplikasi, Server harus mengabaikan transaksi tersebut.
*   **Queue System:** Jika Transaksi banyak terjadi di berbagai tempat menuju ke server, implementasikan queue system di MyRVM-Server (misal: Redis) untuk memastikan urutan pengiriman.

### **6.3. Local Data Security & Image Handling**

*   **Smart Clean:** Mekanisme otomatis untuk menghapus log tua dan file temporary jika disk penuh, tanpa menyentuh file sistem krusial.
*   **Image Handling (Updated):**
    *   Gambar diproses di RAM.
    *   Gambar transaksi dikirim ke **MyRVM-Server** via API `deposit`.
    *   **MyRVM-Server** yang bertanggung jawab mengupload ke **MinIO** dan menyimpan path-nya.
    *   MyRVM-Edge menghapus gambar dari RAM/Temp segera setelah Server merespons "OK" dan Mekanik sinyal GPIO ke Motor Stepper Penjatuh / Penyortir (Sortir ke Bin) merespons "OK".
    *   **Tidak ada kredensial MinIO** yang disimpan di Edge Device.    
*   **SQLite:** Hanya menyimpan log/buffer teks jika offline.
*   **Ephemeral Images:** Gambar yang diambil kamera untuk analisis AI diproses di memori (RAM) dan segera dihapus setelah inferensi selesai. Tidak ada penyimpanan foto botol jangka panjang di disk lokal Jetson (kecuali mode debugging aktif).
*   **Image Storage:** Gambar dari transaksi yang ACCEPTED diunggah sekali ke MyRVM-Server melalui Penyimpanan Objek MINIO. Gambar dari transaksi yang REJECTED disimpan secara lokal selama 24 jam dan kemudian diunggah ke MyRVM-Server melalui Penyimpanan Objek MINIO setiap hari pukul 00:00.
*   **Signed URL Integrity:** URL Kiosk yang didapat dari Handshake harus divalidasi oleh Server. Jika Edge mencoba membuka URL tanpa signature, Server akan me-reject.
*   **Local Socket Binding:** `ws_local.py` **WAJIB** di-bind ke `127.0.0.1` atau `localhost`. Ini mencegah akses kontrol hardware dari luar perangkat (hanya browser lokal yang bisa perintah hardware).
*   **Watchdog Timer:** Sistem menggunakan `systemd` dengan `Restart=always` untuk memastikan jika daemon Python crash, ia akan hidup kembali dalam < 5 detik.
*   **Idempotency:** Setiap transaksi (online/offline) memiliki `client_uuid`. Server akan menolak jika mendeteksi duplikasi data saat proses sinkronisasi ulang.
*   **SQLite Encryption:** Jika database lokal (`local.db`) menyimpan log transaksi yang belum terkirim (offline buffer), pertimbangkan enkripsi ringan atau pastikan file hanya bisa dibaca oleh user `root`.

### **6.4. Device Hardening**
*   **Kiosk Lockdown:** Browser dikunci agar user tidak bisa keluar dari aplikasi web (menutup akses ke OS Desktop).
*   **Port Security:** Menonaktifkan port SSH atau membatasinya hanya untuk akses via VPN (Tailscale) untuk mencegah akses fisik yang tidak sah.
*   **Firewall:** Menggunakan firewall untuk membatasi akses hanya ke port yang diperlukan (misal: 80, 443, 6000, atau port lain yang diperlukan).
*   **Kiosk Mode:** Browser dijalankan di mode kiosk (fullscreen) untuk mencegah akses ke browser lain.
*   **Kiosk Mode Exit:** Memastikan user tidak bisa keluar dari aplikasi web (menutup akses ke OS Desktop). Kecuali, Login via Scanning QR Code sebagai Super Admin, Admin, Operator, dan Teknisi.

---
## 7. Offline Guest Mode & Store-and-Forward

Saat internet mati, UI Kiosk (PWA) akan mendeteksi *Cloud Socket Disconnect* dan beralih ke *Local Bridge*.

### **A. Offline Operational Mode**
1.  **Login Disabled:** QR Code sesi user disembunyikan.
2.  **Guest Mode:** Layar memaksa user menggunakan "Mode Donasi".
3.  **Local Storage:** Keputusan "Accepted" disimpan ke **SQLite Lokal** (`rvm_local.db`).
    ```sql
    INSERT INTO offline_transactions (payload, status) VALUES ('{"items": "mineral", "points": 0}', 'pending');
    ```

### **B. Sync Manager (Forwarding)**
Setelah koneksi pulih:
1.  `ws_client.py` mendeteksi *reconnected*.
2.  `sync_worker.py` membaca SQLite `pending_transactions`.
3.  Kirim bulk data ke `/api/v1/edge/sync-offline`.
4.  Server mengalokasikan poin transaksi tersebut ke akun **System Donation**.

---
## 8. Feature Specifications

### **8.1. Network Manager**
*   **WebSocket Client:** Mempertahankan koneksi persisten ke Laravel Reverb. Auto-reconnect jika putus.
*   **Heartbeat Emitter:** Mengirim status "Online" dan IP Address terkini setiap 60 detik.

### **8.2. Hardware Abstraction Layer (HAL)**
*   Kelas modular untuk mengontrol tipe hardware berbeda (misal: `StepperMotor`, `Servo`, `UltrasonicSensor`,`DHTSensor`).
*   Memungkinkan penggantian hardware tanpa merombak logika utama.

### **8.3. AI Pipeline Engine**
*   **Model Loader:** Memuat model `best.pt` dari disk. Cek hash model saat startup.
*   **Inference:** Menerima frame gambar, mengembalikan JSON: `{class: "mineral", conf: 0.98}`.

### **8.4. Kiosk Manager**
*   Memastikan browser selalu berjalan di layar penuh.
*   Menangani penyembunyian kursor mouse dan pencegahan error pop-up browser.

---

## 9. Development Roadmap

### **Phase 1: Foundation & Provisioning**
*   Setup struktur proyek Python.
*   **Implementasi modul `src/setup_wizard` (FastAPI) untuk import JSON via File Explorer & QR.**
*   Implementasi logika `main.py` untuk deteksi mode (Wizard vs Normal).

### **Phase 2: Foundation & Connectivity**
*   Setup struktur proyek Python (Virtual Env).
*   Implementasi `Handshake` ke API Laravel & Launch Chromium Kiosk.
*   Implementasi Kiosk Launcher.
*   Implementasi WebSocket Client (Connect, Listen, Ping/Pong).

### **Phase 3: Hardware Control (Mockup First)**
*   Implementasi modul GPIO.
*   Membuat simulasi sensor (untuk dev tanpa alat).
*   Integrasi Motor Stepper (PUL/DIR).

### **Phase 4: AI Integration**
*   Implementasi `ai_engine` dengan Acceptance Logic PET vs Not-Empty.
*   Setup PyTorch & Ultralytics di environment Edge (Jetson).
*   Pipeline: Capture Image -> Predict `Validation Logic` -> JSON Output.
*   Implementasi `Validation Logic` (Mineral vs Non-Mineral/Not Empty).
*   Integrasi `Auto Update` system via GitHub Releases + SHA256 Verification.

### **Phase 5: Full System Integration**
*   Menggabungkan semua modul.
*   Implementasi **Local Bridge & PWA Integration** (Offline Guest Mode).
*   Menggabungkan WebSocket listener dengan trigger Hardware.
*   Setup `systemd` service untuk auto-start.
*   Penuntasan `sync_worker` (Store & Forward) dan Stress Test Jaringan.
*   Pengujian mode Offline (SQLite buffering).

---

## 10. Directory Structure

```text
MyRVM-Edge/
├── config/
│   ├── settings.py       # Default settings
│   └── secrets.env       # API Key & Server URL # Generated by Wizard (Contains API Key)
│   └── config.json       # Dynamic config (UI URL, Token) # Generated by Handshake response (UI URL, Tokens)
├── src/
│   ├── setup_wizard/     # [MODUL INSTALASI] # [MODULE] Local Installation UI
│   │   ├── app.py        # Web Server (FastAPI)
│   │   ├── templates/    # HTML: Tombol Upload & QR
│   │   └── static/       # CSS/JS # HTML/JS/CSS for Wizard
│   ├── core/             # Core logic
│   │   ├── provisioning.py # Logic validasi JSON & Save Config # JSON Import Logic
│   │   └── health.py       # Logic Self-Test Hardware # Health Check, psutil, Thermal, Smart Clean logic
│   │   ├── hardware_map.py # Auto-discovery logic (Auto-detect hardware)
│   │   ├── transaction.py# Logic Store (SQLite) & Forward (Sync)
│   │   ├── ai/
│   │   │   ├── engine.py     # YOLO/SAM logic & Validation Logic
│   │   │   └── updater.py    # GitHub Release Checker & Downloader (GitHub Release OTA & SHA256 Verification)
│   │   │   └── models/       # Folder file .pt (best.pt)
│   ├── drivers/          # Hardware drivers (GPIO, Camera)
│   │   ├── gpio_driver.py # GPIO & ESP32 UART Drivers
│   │   └── camera.py # CSI Camera Driver
│   ├── network/
│   │   ├── api_client.py # HTTP Handshake, Heartbeat, Telemetry # Handshake & Deposit Logic
│   │   └── ws_client.py  # WebSocket Listener
│   └── main.py           # Entry Point (Router Mode), Startup Sequence (Boot Logic), Multi-tasking entry point (AsyncIO)
├── data/
│   └── local.db          # SQLite for offline buffer (SQLite for Transaction Buffer)
│   └── temp_images/      # Buffer folder (Cleaned by Smart Clean)
├── logs/                 # Rotation logs
└── requirements.txt      # fastapi, uvicorn, python-multipart, psutil, fastapi, uvicorn, dan lainnya.
```

---

**Dokumentasi Berakhir.**
**Revision Note:** v2.0 Menghilangkan ambiguitas antara mode online dan offline. Menyatukan visi Server-Driven UI dengan Edge-Control-Service.