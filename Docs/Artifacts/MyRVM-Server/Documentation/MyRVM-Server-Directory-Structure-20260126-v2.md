# Artifact: MyRVM-Server Directory Structure Documentation
**Date:** 2026-01-26
**Revision Sequence:** 2
**Reference Change:** Integrasi UUID, Signed URL, dan perbaikan Proxy Trust (HTTPS)
**Revised From:** [MyRVM-Server-Directory-Structure-20260125.md](file:///home/my/MyRVM1/Docs/Artifacts/MyRVM-Server/Documentation/MyRVM-Server-Directory-Structure-20260125.md)
**Context:** Sinkronisasi dokumentasi struktur direktori setelah penambahan fitur keamanan URL dan optimalisasi infrastruktur.

---

# 📁 MyRVM-Server Directory Structure (Revised v2)

## Overview

MyRVM-Server adalah backend PWA (Progressive Web App) untuk sistem Reverse Vending Machine yang dibangun dengan **Laravel 12** dan menggunakan arsitektur MVC (Model-View-Controller). Dokumentasi ini menjelaskan struktur direktori lengkap beserta fungsi setiap folder dan file penting, termasuk pembaruan sistem keamanan UUID dan Signed URL.

## Tech Stack (Update 2026-01-26)

- **Framework:** Laravel 12 (PHP 8.3+)
- **Database:** PostgreSQL (with UUID support)
- **Security:** Laravel Signed URL + Trust Proxies (Cloudflare Native)
- **Cache & Queue:** Redis
- **Authentication:** Laravel Sanctum (API) & Session (Web)
- **Frontend:** Vue.js 3 + Inertia.js (SPA Dashboard) & Blade (Kiosk)

---

## 📂 Updated Directory Structure

```
MyRVM-Server/
├── app/                          # Core Application Logic
│   ├── Http/                     # HTTP Layer
│   │   ├── Controllers/          # Request Handlers
│   │   │   ├── Api/              # REST API Controllers (Edge, CV, User)
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── EdgeDeviceController.php  # IoT Logic & Handshake
│   │   │   │   └── ...
│   │   │   ├── Dashboard/        # Web & Kiosk Controllers
│   │   │   │   ├── MachineController.php   # Admin machine management
│   │   │   │   ├── KioskController.php     # [NEW] Handles Signed Kiosk UI
│   │   │   │   └── ...
│   │   │   └── Api/Kiosk/        # Kiosk Specific APIs (PIN Auth)
│   │   │       ├── AuthController.php      # [FIXED] Technician PIN verification
│   │   │       └── ...
│   │   └── Middleware/           # HTTP Middleware
│   │       ├── ApiLogger.php
│   │       ├── EnsureUserHasRole.php
│   │       └── ValidateRvmApiKey.php       # [NEW] Auth for Edge Devices
│   │
│   ├── Models/                   # Eloquent Models (Database ORM)
│   │   ├── RvmMachine.php       # [UPDATED] Stores UUID for Signed URL
│   │   ├── TechnicianAssignment.php  # [UPDATED] Stores access_pin (plaintext)
│   │   └── ...
│   │
│   └── Providers/               # Service Providers
│       └── AppServiceProvider.php  # [FIXED] Forcing HTTPS scheme behind proxy
│
├── bootstrap/                    # Bootstrap & Middleware Config
│   └── app.php                  # [FIXED] TrustProxy & Middleware Alias
│
├── database/                     # Database Layer
│   ├── migrations/              # Database Migrations
│   │   ├── ...
│   │   ├── 2026_01_18_140800_add_api_key_to_rvm_machines_table.php
│   │   ├── 2026_01_26_104100_add_uuid_to_rvm_machines.php # [NEW]
│   │   └── ...
│   └── seeders/
│       └── DatabaseSeeder.php   # [UPDATED] Setting default machine status
│
├── docker/                         # Docker Configuration
│   └── nginx/                    # Nginx config
│       └── conf.d/
│           └── app.conf          # [FIXED] FastCGI HTTPS params
│
├── routes/                        # Route Definitions
│   ├── api.php                   # [UPDATED] Kiosk V1 prefixes & Edge routes
│   └── web.php                   # [UPDATED] {uuid} parameter + 'signed' middleware
│
├── storage/                       # Application Storage
│   └── api-docs/                 # [UPDATED] Sync manual with UUID format
│       └── api-docs.json
│
└── ...
```

---

## 📋 Key Security Architecture (New)

### 1. Machine Identity Migration
Pencarian mesin di layer publik (Kiosk & External API) yang sebelumnya menggunakan `serial_number` telah dimigrasikan sepenuhnya ke **UUID (Universal Unique Identifier)**.
- **Location:** `app/Models/RvmMachine.php`
- **Impact:** Mencegah *ID Brute-force* atau *URL Guessing*.

### 2. Signed URL Protection
Akses ke antarmuka Kiosk (`/rvm-ui/{uuid}`) sekarang dilindungi oleh middleware `signed` Laravel.
- **Generator:** `URL::signedRoute()` di `EdgeDeviceController`.
- **Validation:** Dilakukan secara otomatis di `routes/web.php`.

### 3. Proxy & SSL Optimization
Konfigurasi `TrustProxies` di `bootstrap/app.php` dan `forceScheme` di `AppServiceProvider` memastikan validasi signature tetap bekerja 100% meskipun server berada di balik Cloudflare atau Nginx Reverse Proxy (HTTPS layer).

---

## 📝 Revision History Log

| Ver | Date | Changes | Author |
| :-- | :-- | :--- | :--- |
| 1.0 | 2026-01-25 | Initial full directory structure documentation | Dev Team |
| 2.0 | 2026-01-26 | Integrated UUID, Signed URL, Kiosk Controller, and HTTPS Proxy fixes | Antigravity |

---
**Last Updated:** 2026-01-26
**Maintained By:** Antigravity (Senior Principal Software Architect)
