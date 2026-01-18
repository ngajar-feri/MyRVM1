Berikut adalah dokumen Project Update dan Spesifikasi Fitur Baru sesuai dengan diskusi terakhir dan struktur menu yang Anda inginkan.

---

# Dokumentasi Pembaruan Proyek & Spesifikasi Fitur Baru: MyRVM v2.1

**Versi Dokumen:** 2.2
**Tanggal Revisi:** Minggu, 18 Januari 2026
**Topik:** Project Update, Requirements Engineering, & Feature Implementation Plan

---

## 1. Project Update & Update Requirements Engineering

Bagian ini merangkum perubahan logika bisnis dan alur kerja berdasarkan evaluasi teknis terakhir.

### A. Perubahan Alur Registrasi Perangkat (Device Registration Flow)
*   **Sebelumnya:** API Key digenerate saat Edge Device ditambahkan. Data teknis diinput manual.
*   **Update:**
    1.  **API Key di RVM Machines:** API Key kini di-generate **seketika** saat Admin membuat data `RVM Machine`. Ini memecahkan masalah "Ayam dan Telur", sehingga teknisi memiliki kunci untuk konfigurasi mesin sebelum mesin dinyalakan.
    2.  **Edge Device Auto-Discovery:** Admin tidak lagi menginput `Hardware ID` atau `Processor Type` secara manual. Data ini diisi otomatis melalui API `/handshake` saat mesin menyala.
    3.  **IP Address Real-time:** IP Address (Local & VPN) dipindahkan ke tabel `EDGE_DEVICES` dan diperbarui secara otomatis melalui mekanisme `/heartbeat` untuk akurasi akses remote.

### B. Pemisahan Konsep "Assignment" vs "Maintenance Ticket"
*   **Analisis:** Ditemukan kerancuan antara "Hak Akses" dan "Perintah Kerja".
*   **Update:**
    1.  **Technician Assignment (Hak Akses):** Bersifat statis/jangka panjang. Menentukan siapa yang *boleh* mengakses mesin tersebut.
    2.  **Maintenance Ticket (Perintah Kerja):** Bersifat transaksional/jangka pendek. Memiliki status (*Pending, In Progress, Completed*) dan log waktu pengerjaan.

### C. Konfigurasi Hardware Hibrida
*   **Update:** Konfigurasi fisik (pin, tipe sensor) dideteksi otomatis (Auto), sedangkan konfigurasi kebijakan (misal: *Threshold* kepenuhan Bin 80%) diatur oleh Admin (Manual) dan disinkronkan ke mesin.

---

## 2. Systematically Set Up: New Feature Implementation

Berikut adalah rancangan implementasi sistematis untuk fitur baru **"Maintenance Ticket System"** dan **"Device Configuration"**, dipetakan ke dalam struktur menu yang Anda minta.

### Struktur Menu Side Bar (Final)

Kami telah memetakan fitur-fitur baru ke dalam struktur 3 menu utama yang Anda tentukan:

1.  **User & Tenants**
    *   **Master Data User & Tenants:** CRUD User, Role management.
    *   **Assignment:** (*Fitur Hak Akses*) Mengatur Teknisi mana memegang RVM mana.
2.  **RVM Machines**
    *   **Master Data RVM Machines:** Register lokasi, Generate API Key, Cek Status Online/Offline.
    *   **Maintenance Tickets:** (*Fitur Baru*) Pelaporan kerusakan dan tracking perbaikan.
3.  **Edge Devices**
    *   **Master Data Edge Devices:** Monitoring detail hardware (SN, IP, CPU), Log Handshake.
    *   **Configuration & Threshold:** (*Fitur Baru*) Mengatur ambang batas sensor dan parameter teknis.

---

### Detail Implementasi Fitur Baru: "Maintenance Tickets"

Fitur ini ditempatkan di bawah menu **RVM Machines**.

#### **A. Planning & Requirements**
*   **Goal:** Melacak riwayat kerusakan dan kinerja teknisi dalam memperbaiki mesin.
*   **Input:** Admin melaporkan masalah (atau auto-generated dari error log).
*   **Process:** Admin memilih Teknisi -> Teknisi menerima notif -> Teknisi fix -> Teknisi close tiket.
*   **Output:** Laporan durasi perbaikan (MTTR - Mean Time To Repair).

#### **B. Database Schema (`maintenance_tickets`)**
```php
Schema::create('maintenance_tickets', function (Blueprint $table) {
    $table->id();
    $table->uuid('ticket_number')->unique(); // Contoh: TKT-202601-001
    
    // Relasi
    $table->foreignId('rvm_machine_id')->constrained('rvm_machines');
    $table->foreignId('assigned_technician_id')->nullable()->constrained('users'); // Teknisi yang ditunjuk
    $table->foreignId('created_by')->constrained('users'); // Admin pelapor
    
    // Detail Masalah
    $table->string('issue_type'); // Enum: 'Sensor Error', 'Motor Jammed', 'Network', 'Full Bin', 'Other'
    $table->text('description');
    $table->string('priority')->default('medium'); // low, medium, high, critical
    
    // Tracking Status
    $table->string('status')->default('pending'); // pending, in_progress, resolved, closed
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    
    // Bukti Penyelesaian
    $table->text('resolution_notes')->nullable();
    $table->string('proof_image_path')->nullable(); // Foto perbaikan

    $table->timestamps();
});
```

#### **C. Implementation Logic (Laravel Controller)**
*   **Store:** Admin membuat tiket. Validasi bahwa Teknisi yang dipilih *sudah* di-assign ke mesin tersebut (cek tabel `technician_assignments`).
*   **Update Status:** Teknisi menekan tombol "Start Job" (update `started_at`).
*   **Complete:** Teknisi upload foto & catatan, status berubah jadi `resolved`.

---

### Detail Implementasi Fitur Baru: "Configuration & Threshold"

Fitur ini ditempatkan di bawah menu **Edge Devices**.

#### **A. Planning & Requirements**
*   **Goal:** Mengontrol perilaku mesin dari jarak jauh tanpa mengubah kode Python.
*   **Key Config:** Batas kepenuhan bin (%), Interval Heartbeat, Timeout Camera.

#### **B. Database Schema Update (`edge_devices` table modification)**
Kita manfaatkan kolom `hardware_config` (JSONB) yang sudah ada, atau tambah kolom khusus `policy_config`. Disarankan menggunakan kolom JSONB terpisah agar tidak tertimpa saat *Handshake*.

```php
// Tambahan pada tabel edge_devices
$table->jsonb('policy_config')->nullable(); 
```
**Struktur JSON `policy_config`:**
```json
{
  "bin_threshold_percent": 85,
  "heartbeat_interval_sec": 60,
  "camera_timeout_sec": 30,
  "maintenance_mode_override": false
}
```

#### **C. UI Implementation (Vue/Blade)**
*   **Lokasi:** Menu *Edge Devices -> Configuration*.
*   **Form:** Input number untuk persentase, toggle switch untuk maintenance mode.
*   **Sync:** Saat tombol "Save" ditekan, server mengirim event WebSocket `config_updated` ke RVM-Edge agar mesin langsung memperbarui variabel lokalnya.

---

### Ringkasan Tabel Menu & Fitur

| Menu Utama | Sub-Menu / Fitur | Deskripsi Fungsi | Tabel Database Utama |
| :--- | :--- | :--- | :--- |
| **User & Tenants** | Master Data | CRUD User & Tenant | `users`, `tenants` |
| | **Assignment** | Mapping Teknisi <-> RVM | `technician_assignments` |
| **RVM Machines** | Master Data | Register Lokasi, API Key | `rvm_machines` |
| | **Maintenance Tickets** | Tiket perbaikan & tracking | `maintenance_tickets` |
| **Edge Devices** | Master Data | Info Hardware (SN, IP) | `edge_devices` |
| | **Configuration** | Set Threshold sensor | `edge_devices` (column: policy) |
| | Telemetry Logs | History data sensor | `rvm_telemetry` |