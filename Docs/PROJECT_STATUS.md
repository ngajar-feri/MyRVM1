# 📊 **MyRVM v1.0 Project Status**

## 🎯 **Project Overview**

**Project Name**: MyRVM v1.0  
**Version:** 1.0  
**Created**: 2026-01-08 08:45:00  
**Status**: ✅ Fresh Initialization

## 🏗️ **Architecture**

### **BareMetal Host PVE:**
1. **vm100 (Docker Host)** - Laravel + File Storage
2. **vm101 (Net Host)** - Network Management
3. **vm102 (CV Machine)** - Pure GPU Computing (NO STORAGE)

### **Reverse Vending Machine (RVM) Components:**
1. **RVM (Jetson Orin Nano)** - Edge AI Processing
2. **Motor DC** - Motor Control untuk buka tutup Alas. Alas terbuka ketika transaksi terpenuhi / Accepted. Kemudian, Botol masuk ke Bak Penampungan.
3. **LED** - LED untuk menerangi Object di dalam kabin saat camera menyala atau siap menerima input / botol (transaksi). **LED berwarna Hijau** akan menyala ketika transaksi diterima dan dimasukkan ke dalam Bak Penampungan kemudian mengirimkan ke Server (vm100) untuk diproses secara ekonomi dan mengubah status transaksi menjadi Accepted.
4. **Sensor DHT** - Sensor untuk mengukur suhu dan kelembaban di dalam kabin.
5. **Bak Penampungan** - tempat penyimpanan botol setelah transaksi selesai.
6. **Sensor Ultrasonic** - Sensor untuk mendeteksi apakah bak penampungan sudah penuh atau tidak.
7. **LCD Touch** - LCD Touchscreen untuk interaksi pengguna. UI / UX yang memudahkan pengguna dalam melakukan transaksi, seperti memilih botol, Login Prompt, melihat status transaksi, dan Interaksi lainnya.

### **Main Applications:**
1. **Server** - MyRVM-Platform (vm100 - Docker Host)
2. **Edge** - MyRVM-Edge (Jetson Orin Nano)
3. **CV Machine** - vm102 (Pure GPU Computing)
4. **Users Apps** - Mobile Applications (Planned)
5. **Tenants Apps** - Tenant Management (Planned)

## 🌐 **Network Configuration**

- **vm100 (Docker Host)**: `100.123.143.87` - Laravel + File Storage
- **vm101 (Net Host)**: Network Management
- **vm102 (CV Machine)**: `100.98.142.94` - Pure GPU Computing
- **RVM (Jetson)**: `100.117.234.2` - Edge AI Processing
- **VPN Network**: Tailscale (Secure clustering)

## 📁 **Project Structure**

```
MySuperApps/
├── MyRVM-Server/          # ✅ Laravel 12 Project (vm100 / Server / Pusat Data)
│   ├── docker-compose.yml    # ✅ Docker Configuration
│   ├── Dockerfile            # ✅ Docker Image
│   ├── .env                  # ✅ Environment Config
│   └── docker/               # ✅ Docker Services
├── MyRVM-Edge/               # ✅ Edge Platform (Jetson Orin Nano)
├── MyRVM-User/               # ✅ Users Apps Platform and Tenants Apps Platform
└── Docs/                     # ✅ Documentation
    ├── 01_SERVER/            # ✅ Server Documentation
    ├── 02_EDGE/              # ✅ Edge Documentation
    ├── 03_USERS_APPS/        # ✅ Users Apps Documentation & Tenants Apps Documentation
    ├── 04_API_ENDPOINT/        # ✅ API Endpoint Documentation
```

## 🚀 **Current Status**
- Server platform (Laravel 12) diinisialisasi dari awal (fresh)
- Docker environment production-ready (Nginx, PHP-FPM, PostgreSQL, Redis, MinIO)
- Landing Pages dan Login Pages sesuai template telah diaktifkan
- Akun demo dibuat untuk verifikasi hak akses awal

## 📊 **Progress Summary**

| Component | Status | Progress |
|-----------|--------|----------|
| **Server (vm100)** | Fresh init | 30% |
| **Edge (Jetson)** | Planned | 0% |
| **CV Machine (vm102 / Independent)** | Planned | 0% |
| **Users Apps** | Planned | 0% |
| **Tenants Apps** | Planned | 0% |
| **Documentation** | Fresh init | 20% |
| **Docker Setup** | Complete | 100% |

## 🔧 **Technical Stack**

### **Backend (vm100 - Docker Host)**
- **Framework**: Laravel 12
- **Database**: PostgreSQL
- **Cache**: Redis
- **Storage**: MinIO (PRIMARY STORAGE)
- **WebSocket**: Laravel Reverb
- **Function**: Main app + File/Image storage

### **Frontend**
- **Framework**: Vue.js 3 + Inertia.js
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **State Management**: Pinia

### **Edge Computing (Jetson)**
- **Hardware**: Jetson Orin Nano
- **AI Framework**: PyTorch + CUDA
- **API**: Flask
- **Models**: YOLO11 + SAM2
- **Function**: Edge AI processing

### **CV Machine (vm102 / Independent)**
- **Hardware**: NVIDIA GPU (RTX 4090/A100)
- **AI Framework**: PyTorch + CUDA
- **API**: Flask/FastAPI
- **Function**: **PURE GPU COMPUTING**
- **Storage**: **NO STORAGE**

### **Mobile**
- **Framework**: Flutter
- **Platform**: iOS + Android
- **Backend**: Laravel API (vm100)

## 🎉 **Achievements**
- Fresh setup Server (Laravel 12) berjalan dengan Docker
- Landing & Login Pages aktif sesuai contoh template
- Akun demo tersedia dan siap uji akses

## 🔧 **Architecture Clarification**

### **vm100 (Docker Host)**
- **Role**: Main application server with Landing Pages, Dashboard, API Management, and User/Tenant Management, Edge Device Management, CV Machine Management, Edge Device System Monitoring and Controlling (door operations, motor tests, LED status, camera controls, local Computing, or remote Computing, Take Picture and Send to Server to Process using GPU-accelerated processing vm102, and return the result), CV Machine System Monitoring, and Log Management
- **Function**: Laravel + File/Image storage
- **Storage**: MinIO object storage
- **Database**: PostgreSQL
- **API**: RESTful APIs

### **vm101 (Net Host)**
- **Role**: Network management, already install Tailscale (Secure clustering)
- **Function**: Network routing, VPN management
- **Storage**: Network configurations only
- **Conection**: Cloudflared for secure access tunneling to vm100 and vm102

### **vm102 (CV Machine)**
- **Role**: Independent CV Machine for PURE GPU computing
- **Function**: AI processing, model training
- **Storage**: **NO STORAGE** - Only computation
- **Purpose**: Heavy AI workloads

---

**Last Updated**: 2026-01-08 08:45:00  
**Version:** 1.0  
**Status**: ✅ Fresh Initialization
