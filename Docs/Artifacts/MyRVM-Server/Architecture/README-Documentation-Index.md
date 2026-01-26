# Documentation: MyRVM-Server Architecture
**Last Updated:** 2026-01-26
**Version:** 1.0 (Comprehensive Build)

---

## 📂 Table of Contents

Selamat datang di pusat dokumentasi teknis **MyRVM-Server**. Dokumen-dokumen di bawah ini mencakup seluruh aspek teknis sistem, mulai dari arsitektur tingkat tinggi hingga detail keamanan.

### 1. [System Overview & Tech Stack](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Architecture/System-Overview-20260126.md)
*   Ringkasan ekosistem pilar (Edge, Server, CV, Apps).
*   Daftar tumpukan teknologi (PHP 8.3, Laravel 12, Vue 3, dll).
*   [Peta direktori aplikasi (Directory Structure v2)](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Documentation/MyRVM-Server-Directory-Structure-20260126-v2.md).

### 2. [Security Design & Authentication](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Architecture/Security-Design-20260126.md)
*   Strategi Signed URL (UUID) untuk Kiosk.
*   API Key Management untuk perangkat Edge.
*   Logika PIN Maintenance dan Rate Limiting.
*   Konfigurasi Trust Proxies (Cloudflare Support).

### 3. [Core API & Integration Points](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Architecture/Core-API-Integration-20260126.md)
*   Mapping endpoint Edge (Handshake, Heartbeat, Telemetry).
*   Kiosk UI Endpoints.
*   Sinkronisasi Model AI.
*   Aliran WebSocket (Real-time events).

### 4. [Database Schema & Entity Relationships](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Architecture/Database-Schema-20260126.md)
*   Visualisasi relasi antar entitas inti.
*   Deskripsi tabel (rvm_machines, edge_devices, dll).
*   Strategi indexing dan optimasi data.

### 5. [Project Blueprint: Integration Roadmap](file:///home/my/MyRVM1/Docs/Project/MyRVM-Server/2026-01-26/Requirements/MyRVM-Server-Roadmap-v1.0.md)
*   Rencana pengembangan fase integrasi Edge & Dashboard.
*   Prioritas fitur MoSCoW (P0-P2).

---

## 🛠️ Maintenance & DevOps Notes
- Seluruh infrastruktur dideploy menggunakan **Docker**.
- Logs tersimpan di `storage/logs/laravel.log`.
- Dokumentasi API Interaktif (Swagger) dapat diakses di `/api/documentation` (tergantung role user).

---
🏗️ **Documentation Master Index Created.**
