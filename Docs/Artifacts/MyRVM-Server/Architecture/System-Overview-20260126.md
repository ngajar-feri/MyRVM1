# Artifact: MyRVM-Server System Overview
**Date:** 2026-01-26
**Revision Sequence:** 1
**Reference Change:** Initial Documentation
**Revised From:** 
**Context:** Proyek Dokumentasi Penuh MyRVM-Server

---

## 1. Executive Summary
**MyRVM-Server** adalah pusat komando (The Hub & Brain) dari ekosistem MyRVM. Server ini bertanggung jawab atas orkestrasi perangkat Edge, manajemen transaksi daur ulang, sistem reward (voucher), dan penyajian data melalui Dashboard Admin berbasis Web.

## 2. System Pillars (The Ecosystem)
Sesuai rancangan arsitektur, MyRVM-Server berinteraksi dengan tiga pilar lainnya:

1.  **RVM-Edge (The Body):** Mesin fisik Jetson-based. Server menyediakan API untuk Handshake, Telemetry, dan Heartbeat.
2.  **RVM-CV (The Trainer):** Stateless GPU Node. Server mengirimkan dataset dan menerima Model AI (`best.pt`) yang sudah dilatih.
3.  **User & Tenant Apps:** Antarmuka bagi pengguna untuk klaim poin dan bagi tenant untuk validasi voucher.

## 3. Technology Stack

| Component | Technology | Role |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 12+ (PHP 8.3) | Core Business Logic & API Hub |
| **Database** | PostgreSQL | Persistent Data Storage |
| **Caching & Queue** | Redis | Session, Cache, & Asynchronous Jobs |
| **Frontend (Web)** | Vue.js 3 + Inertia.js | SPA Dashboard Admin |
| **Authentication** | Laravel Sanctum | Token-based API Auth |
| **Real-time** | Laravel Reverb | WebSocket Telemetry & Alerts |
| **Object Storage** | MinIO (S3 Compatible) | AI Model & Image storage |
| **Deployment** | Docker & Compose | Containerized Infrastructure |

## 4. Logical Architecture (Directory Map)

- `app/Http/Controllers/Api/`: API Endpoints untuk Edge, User, dan CV Server.
- `app/Http/Controllers/Dashboard/`: Logika antarmuka Kiosk dan Dashboard Admin.
- `app/Models/`: Definisi entitas data (RvmMachine, EdgeDevice, TechnicianAssignment, dll).
- `routes/api.php`: Definisi rute API yang diamankan dengan Sanctum atau API Key.
- `routes/web.php`: Rute Dashboard dan antarmuka Kiosk (Signed URL).
- `database/migrations/`: Skema database yang evolusioner (termasuk migrasi UUID terbaru).

---
📝 **Artifact Saved:** `Docs/Artifacts/MyRVM-Server/Architecture/System-Overview-20260126.md`
