Berikut adalah **Project Plan: MyRVM-Edge Versi 1.7** yang lengkap dan telah disesuaikan dengan semua revisi teknis yang disepakati.

---

# Project Plan: MyRVM-Edge (Edge Control Service)

**Version:** 1.7
**Target Platform:** NVIDIA Jetson Orin Nano (JetPack 6.x)
**Role:** Hardware Bridge, AI Inferencing Node, & WebSocket Client.

---

## 1. Executive Summary

**MyRVM-Edge** bukanlah aplikasi web yang berdiri sendiri, melainkan sebuah **Service Daemon** (Layanan Latar Belakang) yang berjalan di perangkat keras RVM. Tugas utamanya adalah menjembatani dunia fisik (sensor, motor, kamera) dengan otak pusat di Cloud (MyRVM-Server). Aplikasi ini mengelola logika kontrol perangkat keras, pemrosesan AI lokal (*on-device*), manajemen pembaruan model secara mandiri, dan proses inisialisasi mesin.

Aplikasi ini tidak merender UI (Antarmuka Pengguna) utama secara lokal. Sebaliknya, ia meluncurkan browser dalam **Mode Kiosk** yang menampilkan antarmuka web dari Server. Namun, untuk **Instalasi Awal (Day-0)**, aplikasi ini menyediakan antarmuka lokal sementara (*Setup Wizard*) untuk konfigurasi kredensial.

---

## 2. Technical Architecture

### **Core Technology**
*   **Language:** Python 3.10+ (AsyncIO).
*   **Architecture Pattern:** Event-Driven Architecture (WebSocket Consumers).

### **Libraries & Dependencies**
1.  **Communication:**
    *   `websockets` atau `python-socketio`: Untuk komunikasi real-time 2 arah dengan Laravel Reverb.
    *   `requests`: Untuk panggilan API HTTP (Handshake, Log Upload, Deposit).
2.  **Hardware Control:**
    *   `gpiod`: Standar Linux modern untuk kontrol GPIO (Motor, Relay).
    *   `pyserial`: Komunikasi UART dengan mikrokontroler (ESP32) jika diperlukan ekspansi IO.
3.  **Computer Vision (AI):**
    *   `ultralytics` (YOLOv11/12): Deteksi Objek Cepat.
    *   `torch` (PyTorch): Backend untuk SAM2 (Segment Anything Model).
    *   `best.pt`: Model deteksi objek yang dioptimalkan yang akan digunakan sebagai model deteksi.
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
    *   Membuka browser lokal ke `http://127.0.0.1:8080`.
*   **Kondisi Provisioned (Siap Operasi):**
    *   Sistem mendeteksi kredensial valid.
    *   Melakukan Handshake ke Server.
    *   Menjalankan Operational Loop.
    *   Meluncurkan browser ke URL UI dari Server (Mode Kiosk).

### **B. Setup Wizard Workflow (Day-0 Installation)**

Antarmuka lokal sederhana berbasis web yang tampil di layar RVM untuk teknisi. Tujuannya adalah mengimpor file `rvm-credentials.json` yang berisi API Key dan Serial Number.

#### **Metode 1: Import via USB (File Explorer)**
1.  Teknisi mencolokkan Flashdisk berisi `rvm-credentials.json`.
2.  Di layar Wizard, Teknisi menekan tombol **"Pilih File Kredensial"**.
3.  Browser membuka jendela **File Explorer** (Dialog native OS).
4.  Teknisi menavigasi ke USB Drive dan memilih file JSON tersebut.
5.  Script memvalidasi isi file dan menyimpannya ke sistem.

#### **Metode 2: Upload via HP (Reverse QR Code)**
*Alternatif jika USB bermasalah.*
1.  Layar RVM menampilkan **QR Code** yang berisi alamat IP lokal Setup Wizard (contoh: `http://192.168.1.50:8080`).
2.  Teknisi memindai QR tersebut menggunakan **Kamera HP** mereka (terhubung ke WiFi yang sama).
3.  HP Teknisi membuka halaman upload sederhana.
4.  Teknisi mengupload file `rvm-credentials.json` dari HP ke RVM via browser HP.

#### **Proses Finalisasi:**
1.  **Validasi:** Script mengecek struktur JSON.
2.  **Hardware Self-Test:** RVM melakukan cek cepat sensor dan motor.
3.  **Save & Reboot:** Kredensial disimpan aman, service restart dan masuk ke Mode Operasional.

### **C. Startup Sequence (Normal Mode)**

1.  **Booting:** Script Python berjalan otomatis.
2.  **Hardware Check:** Scan `/dev/video*`, `lsusb`, GPIO.
3.  **API Handshake:** Kirim data ke `POST /api/v1/edge/handshake` menggunakan kredensial yang sudah tersimpan.
4.  **Model Update Check:** Cek versi `best.pt` via GitHub API.
5.  **Launch UI:** Buka Chromium Kiosk ke URL yang didapat dari respons Handshake.

### **D. Operational Loop (Event Driven)**

*   **Idle:** Menunggu pesan WebSocket dari Server.
*   **Trigger (Server -> Edge):** `session_authorized`, `maintenance_command`.
*   **Trigger (Sensor -> Edge -> Server):** Deteksi objek -> Kamera -> AI.
*   **Transaction Flow:**
    *   AI Inference (YOLO) -> Validation Logic.
    *   Jika `ACCEPTED` -> Trigger Mekanik -> Kirim JSON result ke Server.

---

## 4. Feature Specifications

### **4.1. Provisioning Manager**
Modul ini bertugas menangani input file JSON saat instalasi awal.
*   **Input:** File `rvm-credentials.json`.
    ```json
    {
      "serial_number": "RVM-UI-004",
      "api_key": "Uy00Ku10memyH9kdNOmYvPixrQNWHngauAgO7lu71vmz0CwFVufJwvY842cAmunG",
      "name": "RVM Universitas Indonesia"
    }
    ```
*   **Web Server:** FastAPI/Flask lokal yang hanya aktif saat mode setup.

### **4.2. AI Pipeline Engine & Validation**
*   **Model Loader:** Memuat model `best.pt` dari disk.
*   **Acceptance Logic:**
    *   ✅ **ACCEPTED:** Class `mineral` AND `not_empty` NOT detected.
    *   ❌ **REJECTED:** Class `soda`, `milk`, `yogurt`, `dishwasher`, `non_mineral` OR (`mineral` AND `not_empty` detected).

### **4.3. Auto Update System (OTA)**
*   **Check Update:** Membandingkan versi lokal dengan GitHub Releases.
*   **Download & Verify:** Mengunduh aset `best.pt` dan memverifikasi integritas file menggunakan **SHA256 Checksum** sebelum diterapkan.
*   **Hot-Swap:** Mengganti model di memori.

---

## 5. API Integration & Endpoints (MyRVM-Server)

*   `POST /api/v1/edge/handshake`: Kirim identitas & config hardware.
*   `POST /api/v1/edge/heartbeat`: Ping status rutin.
*   `POST /api/v1/edge/telemetry`: Data sensor.
*   `POST /api/v1/edge/deposit`: Data transaksi & gambar.

---

## 6. Security & Data Protection Measures

### **6.1. Credential Management**
*   **No Hardcoding:** Kredensial di-inject via file JSON saat instalasi.
*   **Storage:** Disimpan di file `.env` lokal yang dilindungi permission file Linux (`chmod 600`) dan tidak di-track oleh git.
*   **Clean Up:** File `rvm-credentials.json` asli dihapus setelah berhasil diimpor.

### **6.2. Local Data Security**
*   **Ephemeral Images:** Gambar diproses di RAM. Upload ke Server -> Server simpan ke MinIO -> Edge hapus dari RAM.
*   **No MinIO Credentials:** Edge tidak menyimpan akses ke MinIO, semua upload gambar via API Laravel.

### **6.3. Device Hardening**
*   **Kiosk Lockdown:** Mencegah akses ke OS Desktop.
*   **Setup Wizard Isolation:** Web server setup hanya bind ke IP lokal atau jaringan maintenance, dimatikan setelah setup selesai.

---

## 7. Development Roadmap

### **Phase 1: Foundation & Provisioning**
*   Setup struktur proyek Python.
*   **Implementasi modul `src/setup_wizard` (FastAPI) untuk import JSON via File Explorer & QR.**
*   Implementasi logika `main.py` untuk deteksi mode (Wizard vs Normal).

### **Phase 2: Connectivity & UI Launch**
*   Implementasi `Handshake` ke API Laravel.
*   Implementasi Kiosk Launcher.
*   Implementasi WebSocket Client.

### **Phase 3: Hardware Control**
*   Modul GPIO & Sensor.
*   Integrasi Motor Stepper.

### **Phase 4: AI & Logic**
*   Setup PyTorch & Ultralytics.
*   Implementasi `Validation Logic` dan `Auto Update` dengan SHA256.

### **Phase 5: Full Integration**
*   Pengujian alur Instalasi dari nol (Flashdisk -> Boot -> Online).
*   Pengujian transaksi penuh.

---

## 8. Directory Structure

```text
MyRVM-Edge/
├── config/
│   ├── settings.py       # Default settings
│   ├── secrets.env       # Dibuat oleh Setup Wizard (berisi API Key)
│   └── config.json       # Dynamic config (UI URL, Token)
├── src/
│   ├── setup_wizard/     # [MODUL INSTALASI]
│   │   ├── app.py        # Web Server (FastAPI)
│   │   ├── templates/    # HTML: Tombol Upload & QR
│   │   └── static/       # CSS/JS
│   ├── core/             # Core logic
│   │   ├── provisioning.py # Logic validasi JSON & Save Config
│   │   └── diagnostics.py  # Logic Self-Test Hardware
│   ├── ai/
│   │   ├── engine.py     # YOLO Inference & Validation
│   │   ├── updater.py    # GitHub Release Checker & Downloader
│   │   └── models/       # Folder file .pt
│   ├── drivers/          # Hardware drivers (GPIO, Camera)
│   ├── network/
│   │   ├── api_client.py # HTTP Client
│   │   └── ws_client.py  # WebSocket Listener
│   └── main.py           # Entry Point (Router Mode)
├── data/
│   └── local.db          # SQLite for offline buffer
├── docs/                 # Documentation
├── tests/                # Unit tests
└── requirements.txt      # Tambah: fastapi, uvicorn, python-multipart
```