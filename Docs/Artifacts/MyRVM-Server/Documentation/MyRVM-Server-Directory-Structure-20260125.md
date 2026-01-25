# Artifact: MyRVM-Server Directory Structure Documentation
**Date:** 2026-01-25
**Revision Sequence:** -
**Reference Change:** -
**Context:** Dokumentasi lengkap struktur direktori MyRVM-Server (PWA Project) untuk referensi pengembangan dan maintenance

---

# 📁 MyRVM-Server Directory Structure

## Overview

MyRVM-Server adalah backend PWA (Progressive Web App) untuk sistem Reverse Vending Machine yang dibangun dengan **Laravel 12** dan menggunakan arsitektur MVC (Model-View-Controller). Dokumentasi ini menjelaskan struktur direktori lengkap beserta fungsi setiap folder dan file penting.

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **Database:** PostgreSQL
- **Cache & Queue:** Redis
- **Authentication:** Laravel Sanctum (Token-based)
- **API Documentation:** L5-Swagger (OpenAPI 3.0)
- **Frontend:** Blade Templates + Vite + Tailwind CSS
- **Storage:** MinIO (S3 Compatible)
- **PDF Export:** DomPDF
- **Excel Export:** Maatwebsite Excel

---

## 📂 Complete Directory Structure

```
MyRVM-Server/
├── app/                          # Core Application Logic
│   ├── Exports/                  # Data Export Classes
│   │   └── ActivityLogExport.php # Excel export untuk Activity Logs
│   │
│   ├── Http/                     # HTTP Layer
│   │   ├── Controllers/          # Request Handlers
│   │   │   ├── Api/              # REST API Controllers
│   │   │   │   ├── AssignmentController.php
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CVController.php
│   │   │   │   ├── EdgeDeviceController.php
│   │   │   │   ├── LogController.php
│   │   │   │   ├── MaintenanceTicketController.php
│   │   │   │   ├── RedemptionController.php
│   │   │   │   ├── RvmMachineController.php
│   │   │   │   ├── TechnicianAssignmentController.php
│   │   │   │   ├── TechnicianController.php
│   │   │   │   ├── TenantVoucherController.php
│   │   │   │   ├── TransactionController.php
│   │   │   │   └── UserController.php
│   │   │   │
│   │   │   ├── Auth/             # Authentication Controllers
│   │   │   │   └── LoginController.php
│   │   │   │
│   │   │   ├── Dashboard/        # Web Dashboard Controllers
│   │   │   │   ├── AssignmentController.php
│   │   │   │   ├── CVServerController.php
│   │   │   │   ├── DeviceController.php
│   │   │   │   ├── LogsController.php
│   │   │   │   ├── MachineController.php
│   │   │   │   ├── TicketController.php
│   │   │   │   └── UserController.php
│   │   │   │
│   │   │   ├── Controller.php   # Base Controller
│   │   │   ├── DashboardController.php
│   │   │   ├── LandingController.php
│   │   │   └── VoucherController.php
│   │   │
│   │   └── Middleware/           # HTTP Middleware
│   │       ├── AllowIframe.php  # Allow iframe embedding (untuk Swagger UI)
│   │       ├── ApiLogger.php    # Log semua API requests
│   │       ├── EnsureUserHasRole.php  # RBAC middleware
│   │       └── SwaggerAuthMiddleware.php  # Auth untuk Swagger UI
│   │
│   ├── Models/                   # Eloquent Models (Database ORM)
│   │   ├── ActivityLog.php      # Log aktivitas user
│   │   ├── AiModelVersion.php   # Versi model AI
│   │   ├── Assignment.php       # Assignment teknisi ke mesin
│   │   ├── EdgeDevice.php       # Perangkat Edge (RVM-Edge)
│   │   ├── EdgeTelemetry.php    # Data telemetry dari Edge
│   │   ├── MaintenanceLog.php   # Log maintenance mesin
│   │   ├── MaintenanceSession.php  # Session maintenance
│   │   ├── MaintenanceTicket.php   # Ticket maintenance
│   │   ├── RvmMachine.php       # Data mesin RVM
│   │   ├── TechnicianAssignment.php  # Assignment teknisi
│   │   ├── TelemetryData.php    # Data telemetry
│   │   ├── Transaction.php      # Transaksi penukaran botol
│   │   ├── TransactionItem.php  # Item dalam transaksi
│   │   ├── User.php             # Model user (extends Authenticatable)
│   │   ├── UserSession.php      # Session user
│   │   ├── UserVoucher.php      # Voucher milik user
│   │   └── Voucher.php          # Master data voucher
│   │
│   ├── Notifications/            # Laravel Notifications
│   │   └── AssignmentCreated.php  # Notifikasi assignment baru
│   │
│   ├── Observers/                # Model Observers
│   │   └── UserObserver.php     # Observer untuk model User
│   │
│   └── Providers/               # Service Providers
│       └── AppServiceProvider.php  # Main service provider
│
├── bootstrap/                    # Bootstrap & Caching
│   ├── app.php                  # Application bootstrap
│   ├── cache/                   # Cache files (auto-generated)
│   │   ├── config.php
│   │   ├── routes.php
│   │   └── services.php
│   └── providers.php            # Service providers registration
│
├── config/                       # Configuration Files
│   ├── app.php                  # Application config
│   ├── auth.php                 # Authentication config
│   ├── cache.php                # Cache config
│   ├── database.php             # Database connection config
│   ├── dompdf.php               # PDF export config
│   ├── filesystems.php          # Storage config (MinIO, Local)
│   ├── l5-swagger.php           # Swagger/OpenAPI config
│   ├── logging.php              # Logging config
│   ├── mail.php                  # Email config
│   ├── queue.php                # Queue config (Redis)
│   ├── sanctum.php              # Sanctum auth config
│   ├── services.php             # Third-party services
│   └── session.php              # Session config
│
├── database/                     # Database Layer
│   ├── factories/               # Model Factories (untuk testing)
│   │   └── UserFactory.php
│   │
│   ├── migrations/              # Database Migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_01_08_041742_create_comprehensive_dashboard_tables.php
│   │   ├── 2026_01_08_042113_create_ai_models_table.php
│   │   ├── 2026_01_08_071239_create_vouchers_table.php
│   │   ├── 2026_01_08_084500_add_role_to_users_table.php
│   │   ├── 2026_01_08_161151_create_personal_access_tokens_table.php
│   │   ├── 2026_01_09_084731_create_telemetry_data_table.php
│   │   ├── 2026_01_09_094838_add_columns_to_rvm_machines_table.php
│   │   ├── 2026_01_09_114655_add_points_to_users_table.php
│   │   ├── 2026_01_09_114741_recreate_transactions_table.php
│   │   ├── 2026_01_09_114948_create_user_vouchers_table.php
│   │   ├── 2026_01_09_115032_create_technician_assignments_table.php
│   │   ├── 2026_01_09_115035_create_maintenance_logs_table.php
│   │   ├── 2026_01_10_010109_create_ai_model_versions_table.php
│   │   ├── 2026_01_10_010109_create_edge_devices_table.php
│   │   ├── 2026_01_10_010109_create_user_sessions_table.php
│   │   ├── 2026_01_10_010111_add_mobile_fields_to_users_table.php
│   │   ├── 2026_01_10_010112_add_location_fields_to_reverse_vending_machines_table.php
│   │   ├── 2026_01_11_084502_add_assigned_by_to_technician_assignments_table.php
│   │   ├── 2026_01_11_091846_add_device_tracking_to_activity_logs_table.php
│   │   ├── 2026_01_11_161400_create_assignments_table.php
│   │   ├── 2026_01_12_055254_add_avatar_to_users_table.php
│   │   ├── 2026_01_12_130807_add_batch_id_to_assignments_table.php
│   │   ├── 2026_01_14_090700_add_status_to_users_table.php
│   │   ├── 2026_01_14_121300_create_edge_telemetry_table.php
│   │   ├── 2026_01_14_180000_add_columns_to_edge_devices_table.php
│   │   ├── 2026_01_14_180100_create_maintenance_sessions_table.php
│   │   ├── 2026_01_15_000000_fix_edge_devices_missing_columns.php
│   │   ├── 2026_01_17_060000_add_soft_deletes_to_edge_devices.php
│   │   ├── 2026_01_18_082926_create_maintenance_tickets_table.php
│   │   ├── 2026_01_18_140800_add_api_key_to_rvm_machines_table.php
│   │   └── 2026_01_20_031857_make_location_nullable_in_rvm_machines.php
│   │
│   └── seeders/                  # Database Seeders
│       ├── ActivityLogSeeder.php
│       ├── DatabaseSeeder.php   # Main seeder (runs all seeders)
│       └── VoucherSeeder.php
│
├── docker/                         # Docker Configuration
│   └── nginx/                    # Nginx config
│       └── conf.d/
│           └── default.conf      # Nginx virtual host config
│
├── public/                        # Public Web Root (Document Root)
│   ├── css/                      # Compiled CSS files
│   │   └── app.css
│   │
│   ├── js/                       # JavaScript Files
│   │   ├── api-helper.js         # Helper untuk API calls
│   │   ├── spa-navigator.js      # SPA navigation handler
│   │   │
│   │   ├── components/           # Reusable JS Components
│   │   │   ├── enhanced-map.js   # Map component
│   │   │   └── tag-autocomplete.js  # Tag autocomplete
│   │   │
│   │   └── modules/              # Feature Modules
│   │       ├── assignments.js    # Assignment management
│   │       ├── cv-servers.js     # CV Server management
│   │       ├── devices.js        # Device management
│   │       ├── logs.js           # Logs viewer
│   │       ├── machines.js       # Machine management
│   │       └── users.js         # User management
│   │
│   ├── vendor/                   # Third-party assets (template, etc)
│   │   └── [vendor assets]       # Admin template files
│   │
│   ├── index.php                 # Application Entry Point
│   ├── robots.txt                # SEO robots file
│   └── .htaccess                 # Apache config (if using Apache)
│
├── resources/                     # Raw Resources (Before Compilation)
│   ├── css/                      # Source CSS
│   │   └── app.css               # Main stylesheet
│   │
│   ├── js/                       # Source JavaScript
│   │   ├── app.js                # Main JS entry point
│   │   └── bootstrap.js           # Bootstrap JS
│   │
│   └── views/                    # Blade Templates
│       ├── auth/                 # Authentication Views
│       │   ├── forgot-password.blade.php
│       │   └── login.blade.php
│       │
│       ├── dashboard/            # Dashboard Views
│       │   ├── admin.blade.php   # Admin dashboard
│       │   ├── api-docs.blade.php # API documentation page
│       │   ├── operator.blade.php  # Operator dashboard
│       │   ├── tenant.blade.php  # Tenant dashboard
│       │   ├── user.blade.php    # User dashboard
│       │   │
│       │   ├── assignments/      # Assignment views
│       │   │   ├── index.blade.php
│       │   │   └── index-content.blade.php
│       │   │
│       │   ├── cv-servers/       # CV Server views
│       │   │   ├── index.blade.php.bak
│       │   │   └── index-content.blade.php.bak
│       │   │
│       │   ├── devices/           # Device views
│       │   │   ├── index.blade.php
│       │   │   └── index-content.blade.php
│       │   │
│       │   ├── logs/             # Logs views
│       │   │   ├── index.blade.php
│       │   │   ├── index-content.blade.php
│       │   │   └── pdf.blade.php  # PDF export template
│       │   │
│       │   ├── machines/         # Machine views
│       │   │   ├── index.blade.php
│       │   │   └── index-content.blade.php
│       │   │
│       │   ├── tickets/           # Maintenance ticket views
│       │   │   ├── index.blade.php
│       │   │   └── index-content.blade.php
│       │   │
│       │   └── users/             # User management views
│       │       ├── index.blade.php
│       │       └── index-content.blade.php
│       │
│       ├── layouts/               # Layout Templates
│       │   └── app.blade.php     # Main application layout
│       │
│       ├── vendor/                # Vendor Views (Third-party)
│       │   └── l5-swagger/        # Swagger UI customization
│       │       ├── .gitkeep
│       │       └── index.blade.php  # Custom Swagger UI with auto-auth
│       │
│       ├── landing.blade.php     # Landing page
│       └── welcome.blade.php      # Welcome page
│
├── routes/                        # Route Definitions
│   ├── api.php                   # API Routes (REST endpoints)
│   ├── console.php               # Artisan console commands
│   └── web.php                   # Web Routes (Dashboard, Auth)
│
├── storage/                       # Application Storage
│   ├── api-docs/                 # Generated API Documentation
│   │   └── api-docs.json         # Swagger/OpenAPI JSON spec
│   │
│   ├── app/                       # Application Files
│   │   ├── private/              # Private files (not web-accessible)
│   │   └── public/               # Public storage (symlinked to public/storage)
│   │
│   ├── fonts/                     # Font files (for PDF generation)
│   │
│   ├── framework/                 # Framework Files
│   │   ├── cache/                # Compiled views, config cache
│   │   │   └── data/             # Cache data
│   │   ├── sessions/             # Session files
│   │   ├── testing/              # Testing files
│   │   └── views/                # Compiled Blade views
│   │
│   └── logs/                      # Application Logs
│       └── laravel.log            # Main log file
│
├── tests/                         # Automated Tests
│   ├── Feature/                   # Feature Tests (Integration)
│   │   ├── ExampleTest.php
│   │   └── Security/
│   │       └── RbacAuditTest.php  # RBAC security tests
│   │
│   ├── Unit/                      # Unit Tests
│   │   └── ExampleTest.php
│   │
│   └── TestCase.php               # Base test case
│
├── .composer/                     # Composer cache (auto-generated)
├── .config/                       # Configuration cache
│   └── psysh/                     # Tinker shell history
│
├── .editorconfig                    # Editor configuration
├── .env                           # Environment variables (not in git)
├── .env.example                   # Environment template
├── .gitignore                     # Git ignore rules
│
├── composer.json                  # PHP Dependencies
├── composer.lock                  # Locked dependency versions
├── package.json                   # Node.js Dependencies
├── package-lock.json              # Locked npm versions
│
├── phpunit.xml                    # PHPUnit test configuration
├── vite.config.js                 # Vite build configuration
│
├── Dockerfile                     # Docker image definition
├── docker-compose.yml             # Docker Compose configuration
│
├── README.md                      # Project documentation
└── README_BACKUP.md              # Backup documentation
```

---

## 📋 Detailed Directory Descriptions

### `/app` - Application Core

**Purpose:** Inti logika aplikasi, mengikuti arsitektur MVC Laravel.

#### `/app/Http/Controllers/Api/`
REST API controllers yang menangani request dari mobile apps dan third-party integrations. Semua endpoint menggunakan Laravel Sanctum untuk authentication.

**Key Controllers:**
- `AuthController.php` - Login, register, token management
- `TransactionController.php` - Transaksi penukaran botol (start, item, commit)
- `RedemptionController.php` - Redeem voucher
- `RvmMachineController.php` - CRUD mesin RVM
- `EdgeDeviceController.php` - Management perangkat Edge
- `TechnicianController.php` - Management teknisi
- `MaintenanceTicketController.php` - Ticket maintenance

#### `/app/Http/Controllers/Dashboard/`
Web dashboard controllers untuk admin panel. Menggunakan Blade templates dan session-based authentication.

**Key Controllers:**
- `UserController.php` - User management
- `MachineController.php` - Machine management
- `DeviceController.php` - Edge device monitoring
- `LogsController.php` - Activity logs viewer
- `TicketController.php` - Maintenance ticket management

#### `/app/Models/`
Eloquent ORM models yang merepresentasikan database tables. Setiap model memiliki relationships, accessors, mutators, dan business logic.

**Key Models:**
- `User.php` - User dengan RBAC (roles: super_admin, admin, technician, tenant, user)
- `Transaction.php` - Transaksi penukaran botol
- `RvmMachine.php` - Data mesin RVM
- `EdgeDevice.php` - Perangkat Edge dengan telemetry
- `Voucher.php` - Master voucher
- `MaintenanceTicket.php` - Ticket maintenance

#### `/app/Http/Middleware/`
HTTP middleware untuk filtering dan processing requests.

**Key Middleware:**
- `ApiLogger.php` - Log semua API requests untuk audit
- `EnsureUserHasRole.php` - RBAC authorization
- `SwaggerAuthMiddleware.php` - Authentication untuk Swagger UI

---

### `/config` - Configuration Files

Semua konfigurasi aplikasi. File-file ini dapat di-override dengan environment variables di `.env`.

**Key Config Files:**
- `l5-swagger.php` - Swagger/OpenAPI documentation config
- `database.php` - Database connections (PostgreSQL)
- `filesystems.php` - Storage config (MinIO, Local)
- `sanctum.php` - Token authentication config
- `cache.php` - Cache config (Redis)

---

### `/database` - Database Layer

#### `/database/migrations/`
Database schema definitions. Migrations diurutkan berdasarkan timestamp untuk memastikan urutan eksekusi yang benar.

**Migration Categories:**
- Core tables: `users`, `cache`, `jobs`
- Business tables: `rvm_machines`, `transactions`, `vouchers`
- Edge integration: `edge_devices`, `edge_telemetry`, `telemetry_data`
- Maintenance: `maintenance_logs`, `maintenance_tickets`, `maintenance_sessions`
- RBAC: `technician_assignments`, `assignments`
- AI: `ai_models`, `ai_model_versions`

#### `/database/seeders/`
Database seeders untuk populate initial data (demo accounts, default vouchers, etc).

---

### `/resources` - Raw Resources

#### `/resources/views/`
Blade templates untuk rendering HTML. Menggunakan layout system dengan `layouts/app.blade.php` sebagai base layout.

**View Structure:**
- `auth/` - Login, forgot password pages
- `dashboard/` - Role-based dashboards (admin, operator, tenant, user)
- `layouts/` - Base layouts
- `vendor/l5-swagger/` - Custom Swagger UI dengan auto-authorization

#### `/resources/css/` & `/resources/js/`
Source files yang akan dikompilasi oleh Vite. CSS menggunakan Tailwind CSS, JS menggunakan vanilla JavaScript dengan modular structure.

---

### `/public` - Web Root

Directory yang diakses langsung oleh web server. Semua file di sini publicly accessible.

**Key Files:**
- `index.php` - Laravel entry point
- `js/api-helper.js` - Frontend API helper
- `js/modules/` - Feature-specific JavaScript modules
- `vendor/` - Third-party assets (admin template)

---

### `/routes` - Route Definitions

#### `api.php`
REST API routes dengan prefix `/api`. Semua routes menggunakan `auth:sanctum` middleware.

**Route Groups:**
- `/api/auth` - Authentication endpoints
- `/api/transactions` - Transaction endpoints
- `/api/machines` - Machine management
- `/api/devices` - Edge device management
- `/api/technicians` - Technician management
- `/api/vouchers` - Voucher management

#### `web.php`
Web routes untuk dashboard dan authentication pages. Menggunakan session-based auth.

**Key Routes:**
- `/` - Landing page
- `/login` - Login page
- `/dashboard` - Role-based dashboard
- `/api/documentation` - Swagger UI

---

### `/storage` - Application Storage

#### `/storage/api-docs/`
Generated Swagger/OpenAPI JSON specification. File ini di-generate oleh command `php artisan l5-swagger:generate`.

#### `/storage/app/`
Application files (uploads, exports, etc). `public/` subdirectory di-symlink ke `public/storage` untuk web access.

#### `/storage/logs/`
Application logs. File `laravel.log` berisi semua application logs.

---

### `/tests` - Automated Tests

#### `/tests/Feature/`
Integration tests yang test complete features end-to-end.

**Key Tests:**
- `Security/RbacAuditTest.php` - RBAC security audit tests

#### `/tests/Unit/`
Unit tests untuk individual components.

---

## 🔑 Key Files Reference

### Configuration Files
- `.env` - Environment variables (database, cache, storage, etc)
- `composer.json` - PHP dependencies
- `package.json` - Node.js dependencies
- `vite.config.js` - Vite build configuration
- `docker-compose.yml` - Docker services configuration

### Entry Points
- `public/index.php` - Application entry point
- `routes/api.php` - API route definitions
- `routes/web.php` - Web route definitions

### Documentation
- `README.md` - Project overview
- `storage/api-docs/api-docs.json` - OpenAPI specification
- `config/l5-swagger.php` - Swagger configuration

---

## 🚀 Development Workflow

### Adding New Feature

1. **Create Migration:** `php artisan make:migration create_feature_table`
2. **Create Model:** `php artisan make:model Feature`
3. **Create Controller:** `php artisan make:controller Api/FeatureController`
4. **Define Routes:** Add routes in `routes/api.php`
5. **Create Views (if needed):** Create Blade templates in `resources/views/`
6. **Update API Docs:** Add Swagger annotations in Controller
7. **Generate Docs:** `php artisan l5-swagger:generate`

### File Organization Best Practices

- **Controllers:** Group by feature (Api/, Dashboard/, Auth/)
- **Models:** One model per table, keep relationships clear
- **Views:** Mirror controller structure in `resources/views/`
- **JavaScript:** Modular structure in `public/js/modules/`
- **Migrations:** Use descriptive names with timestamps

---

## 📝 Notes

- **PWA Features:** Application dapat diinstall sebagai PWA melalui service worker (future implementation)
- **API Documentation:** Always update Swagger annotations when modifying API
- **Storage:** Use MinIO for production file storage, local for development
- **Caching:** Redis digunakan untuk cache dan queue
- **Logging:** All API requests logged via `ApiLogger` middleware

---

**Last Updated:** 2026-01-25
**Maintained By:** Development Team