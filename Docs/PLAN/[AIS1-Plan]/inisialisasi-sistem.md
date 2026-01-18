urutan logis untuk **inisialisasi sistem** agar relasi data (Foreign Key) terjaga integritasnya.

---

### **1. Urutan & Alur Data (Flow)**

#### **Langkah 1: Add RVM Machines (Logical Entity)**
*   **Aktor:** Super Admin / Admin (Via Dashboard).
*   **Tujuan:** Mendaftarkan "Lokasi" atau "Identitas Mesin" ke dalam database untuk mendapatkan **API Key**.
*   **Proses:**
    1.  Admin input Nama Lokasi & Koordinat Map.
    2.  System generate `UUID` dan `API_KEY` (rahasia).
    3.  Data disimpan ke tabel `rvm_machines`.
    4.  **PENTING:** System secara otomatis membuat *stub* (record kosong) di tabel `edge_devices` yang terhubung ke mesin ini, menunggu handshake.

#### **Langkah 2: Add Edge Devices (Physical Entity)**
*   **Aktor:** Mesin RVM (Jetson) secara Otomatis (via API Handshake).
*   **Tujuan:** Mengikat perangkat fisik (Hardware ID) dengan RVM Machine yang sudah dibuat.
*   **Proses:**
    1.  Teknisi memasukkan `API_KEY` (dari Langkah 1) ke file `.env` di Jetson.
    2.  Mesin menyala (Booting).
    3.  Python Script mengirim request ke API Server (`/handshake`) membawa `hardware_id` dan `hardware_config` (JSON).
    4.  Server mengupdate record di tabel `edge_devices` yang berelasi dengan `API_KEY` tersebut.
    5.  Status mesin berubah menjadi **🟢 Online**.

#### **Langkah 3: Add Assignment User (Operational)**
*   **Aktor:** Super Admin.
*   **Tujuan:** Memberikan hak akses kepada **Teknisi** tertentu untuk mengelola mesin tersebut (misal: untuk generate PIN Maintenance).
*   **Proses:**
    1.  Admin memilih User (Role: Teknisi).
    2.  Admin memilih RVM Machine.
    3.  Simpan ke tabel pivot/relasi `technician_assignments`.

---

### **2. Database Schema (Laravel Migrations)**

Berikut adalah struktur tabel yang **konsisten** dengan variabel yang telah kita diskusikan (menggunakan `JSONB` PostgreSQL).

#### **A. Tabel `rvm_machines`**
Fokus pada Logika Bisnis, Lokasi, dan Kredensial.

```php
Schema::create('rvm_machines', function (Blueprint $table) {
    $table->id();
    
    // Identitas
    $table->uuid('uuid')->unique(); // ID publik (misal untuk QR Code)
    $table->string('name'); // Contoh: "RVM Mall Grand Indonesia"
    
    // Kredensial (Untuk dipakai oleh Edge Device)
    $table->string('api_key', 64)->unique(); // Kunci rahasia komunikasi
    
    // Lokasi (Untuk Peta)
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->text('address')->nullable();
    
    // Status Operasional
    // Enum: 'active', 'maintenance', 'inactive', 'full'
    $table->string('status')->default('inactive'); 
    
    $table->timestamps();
    $table->softDeletes(); // Agar history transaksi tidak hilang jika mesin dihapus
});
```

#### **B. Tabel `edge_devices`**
Fokus pada Fisik Hardware, IP, dan Konfigurasi Teknis.

```php
Schema::create('edge_devices', function (Blueprint $table) {
    $table->id();

    // Relasi 1-to-1: Satu Lokasi RVM hanya punya satu Otak (Edge) aktif
    $table->foreignId('rvm_machine_id')
          ->unique() 
          ->constrained('rvm_machines')
          ->onDelete('cascade');

    // Identitas Fisik (Didapat dari Handshake)
    $table->string('hardware_id')->unique()->nullable(); // Serial Number Jetson
    $table->string('processor_type')->default('Jetson Orin Nano');
    
    // Networking (Untuk Remote Access Teknisi)
    $table->string('ip_address_vpn')->nullable(); // Tailscale IP
    $table->string('ip_address_local')->nullable(); // LAN IP

    // Konfigurasi Detail (JSONB PostgreSQL)
    // Berisi: Cameras (ID, Path), Sensors, Actuators, Firmware Version
    $table->jsonb('hardware_config')->nullable(); 

    // Monitoring
    $table->timestamp('last_heartbeat_at')->nullable();
    
    $table->timestamps();
});
```

#### **C. Tabel `technician_assignments`**
Tabel Pivot untuk menghubungkan User (Teknisi) dengan Mesin. Ini memungkinkan satu teknisi memegang banyak mesin, dan satu mesin bisa dipegang beberapa teknisi (jika shift).

```php
Schema::create('technician_assignments', function (Blueprint $table) {
    $table->id();

    // Relasi ke User (Yang punya Role: Technician)
    $table->foreignId('user_id')
          ->constrained('users')
          ->onDelete('cascade');

    // Relasi ke Mesin RVM
    $table->foreignId('rvm_machine_id')
          ->constrained('rvm_machines')
          ->onDelete('cascade');

    // Opsional: Validitas penugasan
    $table->timestamp('assigned_at')->useCurrent();
    $table->timestamp('expires_at')->nullable(); // Jika penugasan sementara

    // Mencegah duplikasi penugasan aktif yang sama
    $table->unique(['user_id', 'rvm_machine_id']);
});
```

### **3. Ringkasan Relasi Model (Eloquent)**

*   **RvmMachine**
    *   `hasOne` **EdgeDevice**
    *   `belongsToMany` **User** (via `technician_assignments`)
*   **EdgeDevice**
    *   `belongsTo` **RvmMachine**
*   **User (Teknisi)**
    *   `belongsToMany` **RvmMachine** (via `technician_assignments`)

Desain ini sudah memenuhi prinsip normalisasi database dan mendukung fitur **Auto-Discovery** (JSONB) serta **Security** (Pemisahan tabel Kredensial Mesin dan Fisik Mesin).