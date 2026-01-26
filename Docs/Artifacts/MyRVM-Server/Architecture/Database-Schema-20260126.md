# Artifact: MyRVM-Server Database Schema
**Date:** 2026-01-26
**Revision Sequence:** 1.0
**Context:** Dokumentasi Struktur Data Inti

---

## 1. Core Entities Relationship

Skema database dirancang untuk mendukung skalabilitas dan auditabilitas.

### A. Mesin & Perangkat (Hardware Layer)
- **`rvm_machines`**: Tabel induk mesin. Menyimpan `serial_number`, `uuid` (unique access), `api_key`, dan status operasional.
- **`edge_devices`**: Metadata hardware Jetson (CPU/GPU usage, thermal, IP Address). Berelasi 1:1 ke `rvm_machines`.
- **`telemetry_data`**: Riwayat sensor (Capacity, Status) dengan interval waktu.

### B. Maintenance & Workforce (Management Layer)
- **`users`**: Tabel profil (Admin, Teknisi, dll).
- **`technician_assignments`**: Pivot table antara `users` & `rvm_machines`. Menyimpan `access_pin` dan log akses teknisi.
- **`maintenance_tickets`**: Riwayat kerusakan dan perbaikan mesin oleh teknisi.

### C. Transactions & Rewards (Business Layer)
- **`recycling_sessions`**: Sesi aktif user saat memasukkan botol.
- **`transactions`**: Log item yang berhasil didebet menjadi poin.
- **`vouchers`**: Inventori reward (milik tenant) yang bisa diklaim oleh user.

## 2. Important Fields & Data Types

| File | Column | Type | Purpose |
| :--- | :--- | :--- | :--- |
| `rvm_machines` | `uuid` | `UUID` | Secure Signed URL identifier. |
| `rvm_machines` | `api_key` | `String(64)` | Edge Handshake authentication. |
| `technician_assignments` | `access_pin` | `String(6)` | PIN Maintenance (Plaintext - Temporary). |
| `telemetry_data` | `payload` | `JSONB` | Flexible sensor data storage. |

## 3. Indexing Strategy
- Index pada `uuid` & `serial_number` untuk pencarian mesin yang cepat.
- Index pada `status` & `created_at` untuk filtering dashboard dan laporan periodik.

---
📝 **Artifact Saved:** `Docs/Artifacts/MyRVM-Server/Architecture/Database-Schema-20260126.md`
