Tentu, ini adalah rekomendasi desain dan struktur **Formulir Registrasi Perangkat Edge Baru (Register New RVM)** pada Dashboard Admin (Laravel 12 + Vue/Blade).

Formulir ini dirancang untuk mengisi data ke tabel `RVM_MACHINES` dan membuat record awal di `EDGE_DEVICES`.

---

### **Desain UI/UX Formulir**

Formulir sebaiknya dibagi menjadi **3 Bagian Utama (Card/Section)** agar rapi dan logis.

#### **Bagian 1: Identitas & Status Mesin**
Bagian ini mendefinisikan "Siapa" dan "Bagaimana" status awal mesin ini.

| Label Input | Tipe Input | Wajib? | Keterangan / Validasi |
| :--- | :--- | :--- | :--- |
| **Nama Lokasi / Unit** | `Text Input` | **Yes** | Contoh: *"RVM Lobi Mall Grand Indonesia"*. Harus Unik. |
| **Kode Aset (Inventory ID)** | `Text Input` | No | ID internal perusahaan (misal: `INV-2026-001`). Berbeda dengan UUID sistem. |
| **Status Awal** | `Dropdown` | **Yes** | Pilihan: `Maintenance` (Default), `Inactive`. *Jangan set `Online` manual, biarkan sistem yang mendeteksi heartbeat (Pinging).* |
| **Deskripsi / Catatan** | `Textarea` | No | Catatan khusus untuk teknisi (misal: "Posisi di dekat ATM Center"). |
| **ReGenerate API Key** | `Button` | No | Membuat string acak (misal 64 karakter) untuk `api_key`. **Ini hanya ditampilkan SATU KALI** ada fasilitas copy paste text. ada tombol [Download Config File (.json)] (Opsional, untuk memudahkan teknisi) |

#### **Bagian 2: Geolokasi (Peta Interaktif)**
Sangat penting untuk fitur peta di Aplikasi User. Jangan biarkan Super Admin atau Admin mengetik koordinat manual jika memungkinkan (mirip seperti popup form RVM Installation Assignment -> Installation Location).

| Label Input | Tipe Input | Wajib? | Keterangan / Validasi |
| :--- | :--- | :--- | :--- |
| **Pilih Lokasi** | **Map Widget** | **Yes** | Integrasi Open Street Map/Leaflet/Google Maps/Mapbox. Super Admin atau Admin menaruh **Pin**, sistem otomatis mengisi Lat/Long. |
| **Latitude** | `Number (Decimal)` | **Yes** | Terisi otomatis dari Pin Peta. _Read-only_. |
| **Longitude** | `Number (Decimal)` | **Yes** | Terisi otomatis dari Pin Peta. _Read-only_. |
| **Alamat Lengkap** | `Textarea` | **Yes** | Alamat tekstual untuk ditampilkan ke User. Bisa *auto-fill* dari *Reverse Geocoding* Open Street Map. |

#### **Bagian 3: Konfigurasi Perangkat Keras Awal (Opsional / Advanced)**
Ini akan disimpan ke kolom `hardware_config` (JSONB) di tabel `EDGE_DEVICES`.

| Label Input | Tipe Input | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| **Tipe Kontroler** | `Dropdown` | **Yes** | Default: `NVIDIA Jetson`. Opsi lain: `RaspberryPI`,`Arduino`, `ESP32`,`ESP8266`,`STM32`, `lainnya`. Jika Lainnya, harap ada fitur untuk menambahkan hardware dan masuk ke Tabel `EDGE_HARDWARE`. |
| **Kamera ID** | `Text Input` | No | Merk Kamera atau Serial number kamera atau ID hardware (misal: `UGREEN`,`Hikvision`,`/dev/video0`). |
| **Ambang Batas Penuh (%)**| `Number` | **Yes** | Default: `90`. Persentase kapasitas bin sebelum status berubah jadi `Full`. |
| **Target Model AI** | `Dropdown` | No | Pilih versi model Default: `best.pt` spesifik jika ingin menimpa default global. |

---

### **Alur Logika Backend (Laravel Controller)**

Saat Admin menekan tombol **"Simpan & Generate API Key"**, berikut yang terjadi di `RvmMachineController@store`:

1.  **Validasi:** Cek keunikan Nama dan format koordinat.
2.  **Generate UUID:** Membuat UUIDv4 untuk `uuid` mesin.
3.  **Generate API Key:** Membuat string acak (misal 64 karakter) untuk `api_key`. **Ini hanya ditampilkan SATU KALI** ada fasilitas copy paste text.
4.  **Insert ke DB:**
    *   Insert ke tabel `RVM_MACHINES`.
    *   Insert record awal ke tabel `EDGE_DEVICES` (relasi 1:1) dengan konfigurasi default.
5.  **Return Response:** Menampilkan modal sukses berisi **API Key** dan **Device ID (UUID)**.

---

### **Mockup Respon (Modal Sukses)**

Setelah submit, Admin **wajib** melihat tampilan ini karena API Key tidak bisa dilihat lagi (jika di-hash):

> **✅ Mesin Berhasil Didaftarkan!**
>
> Harap salin kredensial di bawah ini dan masukkan ke konfigurasi file `.env` pada Jetson Orin Nano (RVM-Edge).
>
> *   **Nama Unit:** RVM Lobi Mall GI
> *   **Device ID (UUID):** `550e8400-e29b-41d4-a716-446655440000`
> *   **API Key:** `rvm_live_sk_89237489237489234...` (Tombol Copy)
>
> *Peringatan: API Key ini tidak akan ditampilkan lagi demi keamanan.*
>
> [Download Config File (.json)] (Opsional, untuk memudahkan teknisi)

---

### **Implementasi Kode (Contoh Struktur Request Laravel)**

```php
// StoreRvmRequest.php

public function rules(): array
{
    return [
        'name' => 'required|string|max:255|unique:rvm_machines,name',
        'status' => 'required|in:maintenance,inactive', // Baru dibuat pasti maintenance/inactive
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'address' => 'required|string',
        'hardware_config.threshold_full' => 'integer|min:50|max:100',
        'hardware_config.camera_source' => 'string|nullable',
    ];
}
```

### **Rekomendasi Utama: Tabel Khusus `maintenance_sessions`**

Jangan simpan PIN ini di tabel `rvm_machines` sebagai kolom statis. Karena kita butuh *audit trail* D:\~dev\MyReverseVendingMachine1\MySuperApps\MyRVM-Server\app\Http\Controllers\Api\LogController.php (siapa yang minta, kapan, dan untuk mesin mana) serta manajemen kadaluarsa (*expiration*).

Buat tabel baru bernama **`maintenance_sessions`** atau **`rvm_access_tokens`**.

#### **Skema Database (Migrasi Laravel)**

```php
Schema::create('maintenance_sessions', function (Blueprint $table) {
    $table->id();
    // Relasi ke mesin yang mau di-maintenance
    $table->foreignId('rvm_machine_id')->constrained('rvm_machines')->onDelete('cascade');
    
    // Relasi ke Teknisi/Admin yang merequest PIN ini
    $table->foreignId('technician_id')->constrained('users'); 

    // PIN yang di-hash (SANGAT PENTING: Jangan simpan plain text)
    $table->string('pin_hash'); 
    
    // Kapan PIN ini kadaluarsa (misal: 1 jam setelah generate)
    $table->timestamp('expires_at');
    
    // Kapan PIN ini sukses dipakai login di mesin (untuk audit)
    $table->timestamp('used_at')->nullable();
    
    $table->timestamps();
});
```

### **Optional: TOTP (Time-based One-Time Password)**
*Jika Anda ingin RVM bisa masuk mode maintenance **tanpa koneksi internet (Offline)**.*

Jika lokasi RVM sering putus koneksi internet, metode database di atas tidak akan jalan (karena RVM tidak bisa tanya ke server apakah PIN valid).

**Solusinya:** Gunakan konsep seperti **Google Authenticator**.
1.  Di tabel `rvm_machines`, tambahkan kolom `maintenance_secret_key` (dienkripsi).
2.  Teknisi punya aplikasi khusus (bisa di menu "Tenant App" atau app authenticator biasa) yang men-generate kode 6 digit berdasarkan waktu saat ini dan Secret Key mesin tersebut.
3.  RVM-Edge (Python) juga punya Secret Key yang sama di config lokalnya.
4.  RVM memvalidasi PIN secara lokal (Offline) menggunakan algoritma TOTP.

**Kesimpulan:**
Untuk **MyRVM** yang mengandalkan konektivitas dan monitoring real-time, **Opsi 1 (Tabel Database `maintenance_sessions`)** adalah yang terbaik karena memberikan kontrol terpusat, bisa dicabut (revoke) kapan saja oleh Admin, dan memiliki log audit yang lengkap.