Berikut adalah **Flow (Alur Kerja)** yang paling logis dan **Schema Database Final** berdasarkan pemisahan konsep yang baru Anda tetapkan.

---

### **1. Flow (Urutan Eksekusi Data)**

Urutan yang benar adalah **Top-Down** (Dari Logis ke Fisik, lalu ke Operasional).

#### **Langkah 1: Create RVM Machine (Logical)**
*   **Siapa:** Admin Dashboard.
*   **Aksi:** Input Nama Lokasi & Koordinat.
*   **Output:** System generate **UUID** & **API Key**.
*   **Status:** *Inactive*.

#### **Langkah 2: Physical Installation (Edge Device)**
*   **Siapa:** Teknisi di Lokasi.
*   **Aksi:** Pasang Jetson -> Input API Key (dari Langkah 1) -> Nyalakan.
*   **System:** Mesin melakukan **Handshake**.
*   **Output:** Tabel `edge_devices` terisi otomatis (Hardware ID, IP, dll).
*   **Status:** Berubah jadi *Active/Online*.

#### **Langkah 3: Assignment Hak Akses (Static)**
*   **Siapa:** Admin Dashboard.
*   **Aksi:** Memilih Teknisi A untuk "memegang" RVM Mall A.
*   **Fungsi:** Mulai detik ini, RVM Mall A muncul di list aplikasi Teknisi A. Dia bisa melihat status baterai, suhu, dan generate PIN Maintenance kapan saja tanpa perlu tiket.

#### **Langkah 4: Maintenance Ticket (Transactional)**
*   **Kondisi:**
    *   *Skenario A (Instalasi Baru):* Admin buat tiket "Tolong Instalasi Fisik".
    *   *Skenario B (Kerusakan):* Sensor rusak -> Admin buat tiket "Perbaikan Sensor".
*   **Aksi:** Admin assign tiket ke Teknisi A (yang sudah punya Hak Akses di Langkah 3).
*   **Proses:** Teknisi terima notif -> Klik "Start" -> Kerjakan -> Upload Foto -> Klik "Complete".

catatan tambahan: konsepnya
1. Admin Dashboard: Hanya melakukan Create RVM Machine (Nama & Lokasi) untuk mendapatkan API KEY.
2. Di Lapangan: API Key tersebut dimasukkan ke mesin fisik.
3. Sistem (Backend): Saat mesin mengirim data (Handshake) menggunakan API Key tersebut, sistem otomatis tahu: "Oh, hardware ini (SN: XYZ, IP: 1.2.3.4) adalah milik RVM Machine 'Mall Grand Indonesia' (karena API Key-nya cocok)."
Jadi, TIDAK ADA form input manual di Dashboard untuk mendaftarkan Edge Devices (Serial Number, IP, atau Tipe Prosesor Edge Device, dan lain-lain). Data tersebut otomatis terisi/terkait ke RVM Machine yang sesuai begitu mesin berhasil connect menggunakan API Key yang benar.
4. Halaman http://localhost:8000/dashboard/machines -> List RVM Machine -> Klik Details -> Tampilkan Detail RVM Machine (termasuk Edge Devices yang terhubung). Sudah benar! Kemudian Edge Devices yang muncul di halaman tersebut memiliki tampilan informasi: Device ID, Controller, Status, Tailscale IP, Local IP, Camera, AI Model.
kemudian tampilan juga berupa card dari Health Metrics.
5. Menu Edge Devices di hilangkan saja. Tidak perlu ada. karena sudah ada di halaman Detail RVM Machine.
---

### **2. Database Schema (Laravel Migrations)**

Berikut adalah struktur tabel yang sudah disesuaikan dengan kebutuhan **Hak Akses Tetap** vs **Tiket Tugas**.

#### **A. Tabel `technician_assignments` (Hak Akses Tetap)**
*Tabel pivot sederhana untuk otorisasi.*

```php
Schema::create('technician_assignments', function (Blueprint $table) {
    $table->id();
    
    // Siapa Teknisinya?
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    
    // Mesin mana yang boleh dia akses?
    $table->foreignId('rvm_machine_id')->constrained('rvm_machines')->onDelete('cascade');
    
    // Kapan akses diberikan?
    $table->timestamp('assigned_at')->useCurrent();
    
    // Kapan akses dicabut? (Nullable = Permanen sampai dihapus manual)
    $table->timestamp('expires_at')->nullable();

    // Mencegah duplikasi: 1 Teknisi tidak perlu di-assign 2x ke mesin yang sama
    $table->unique(['user_id', 'rvm_machine_id']);
});
```

#### **B. Tabel `maintenance_tickets` (Work Order / Tugas)**
*Tabel transaksi untuk pelaporan dan tracking pekerjaan.*

```php
Schema::create('maintenance_tickets', function (Blueprint $table) {
    $table->id();
    
    // Identitas Tiket (Contoh: TKT-202601-005)
    $table->string('ticket_number')->unique();
    
    // Relasi
    $table->foreignId('rvm_machine_id')->constrained('rvm_machines');
    $table->foreignId('created_by')->constrained('users'); // Siapa pelapornya? (Admin/System)
    
    // Teknisi yang mengerjakan (Nullable dulu, siapa tahu belum di-assign)
    // Validasi di Controller: User ini HARUS ada di tabel technician_assignments mesin terkait
    $table->foreignId('assignee_id')->nullable()->constrained('users'); 

    // Detail Masalah
    // Enum: 'Installation', 'Sensor Fault', 'Motor Jammed', 'Network Issue', 'Full Bin', 'Other'
    $table->string('category'); 
    $table->text('description');
    $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
    
    // Tracking Status
    // Flow: pending -> assigned -> in_progress -> resolved -> closed
    $table->string('status')->default('pending');
    
    // Timeline Pengerjaan
    $table->timestamp('assigned_at')->nullable(); // Waktu Admin klik assign
    $table->timestamp('started_at')->nullable();  // Waktu Teknisi klik "Start Job" di app
    $table->timestamp('completed_at')->nullable(); // Waktu Teknisi klik "Complete"
    
    // Bukti Penyelesaian (Evidence)
    $table->text('resolution_notes')->nullable(); // Catatan teknisi: "Kabel kendor, sudah dikencangkan"
    $table->string('proof_image_path')->nullable(); // Foto hasil perbaikan
    
    $table->timestamps();
    $table->softDeletes();
});
```

---

### **3. Implementasi Logika (Rules)**

Agar sistem berjalan sesuai desain, ada satu *Business Rule* penting yang harus diterapkan di Controller Laravel saat membuat Tiket:

**Rule:**
> *"Admin hanya boleh menugaskan Tiket (assignee_id) kepada Teknisi yang SUDAH memiliki Hak Akses (ada di tabel `technician_assignments`) pada RVM tersebut."*

**Contoh Logic (Laravel):**
```php
// Saat Assign Ticket
$isAuthorized = TechnicianAssignment::where('rvm_machine_id', $request->rvm_id)
                ->where('user_id', $request->technician_id)
                ->exists();

if (!$isAuthorized) {
    return error("Teknisi ini belum memiliki izin akses ke Area/Mesin ini. Silahkan tambahkan di menu Assignment terlebih dahulu.");
}
```

Struktur ini sangat rapi, memisahkan keamanan (siapa boleh masuk) dengan operasional (siapa mengerjakan apa).