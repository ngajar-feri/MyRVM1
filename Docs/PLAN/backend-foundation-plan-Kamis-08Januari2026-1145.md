Versi Dokumen: 1.0
Tanggal Revisi: Kamis-08 Januari 2026 - 11:45 AM
Tujuan: Mendefinisikan roadmap modernisasi backend dari Monolith menjadi Hybrid API-First architecture.
Status: Selesai

# Rencana Pengembangan Backend Foundation (MyRVM-Server)

## 1. Pendahuluan
Dokumen ini menggantikan rencana konseptual lama yang ada di `Docs/Overview1`. Fokus utama saat ini adalah membangun fondasi RESTful API yang kuat, aman, dan terdokumentasi untuk mendukung integrasi dengan Mobile Apps (User/Tenant) dan Perangkat IoT (RVM/Edge).

## 2. Gap Analysis (Kondisi Saat Ini vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (To-Be) |
| :--- | :--- | :--- |
| **Arsitektur** | Monolith (Web-only) | Hybrid (Web + RESTful API) |
| **Routing** | Tercampur di `web.php` | Terpisah: `web.php` (UI) & `api.php` (Service) |
| **Auth** | Session-based (Cookie) | Token-based (Sanctum/JWT) untuk API |
| **Dokumentasi** | Tidak ada | Swagger/OpenAPI (Auto-generated) |
| **Standardisasi** | Return format tidak konsisten | JSON Standard (JSend/Envelope pattern) |

## 3. Spesifikasi Teknis API

### A. Standar Protokol
*   **Format Data**: JSON (`application/json`)
*   **Methods**: GET, POST, PUT, DELETE, PATCH
*   **Versioning**: URL Prefix `/api/v1/...`

### B. Keamanan
*   **Autentikasi**: Laravel Sanctum (Bearer Token).
*   **Rate Limiting**: 60 requests/minute per user/IP.
*   **Input Validation**: Menggunakan Laravel FormRequest.

### C. Struktur Response Standar
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "errors": null
}
```

## 4. Roadmap Implementasi

### Fase 1: Foundation (Target: Hari Ini)
1.  **Instalasi API Scaffolding**: Mengaktifkan `routes/api.php` dan Sanctum.
2.  **API Auth**: Endpoint Login/Logout/Register via API.
3.  **Dokumentasi**: Setup L5-Swagger.

### Fase 2: Core Modules
1.  **Voucher API**: Refactoring modul voucher tenant agar bisa diakses mobile apps.
2.  **User Profile**: Update profil dan password via API.

### Fase 3: IoT Integration (Next Sprint)
1.  **Telemetry Endpoint**: Menerima data sensor dari ESP32/Jetson.
2.  **OTA Update**: Endpoint untuk cek versi firmware.

## 5. Log Perubahan
*   [08-01-2026 11:45] Inisiasi dokumen rencana baru.
