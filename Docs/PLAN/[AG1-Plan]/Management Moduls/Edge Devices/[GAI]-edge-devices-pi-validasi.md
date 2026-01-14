Pertanyaan yang sangat krusial untuk aspek keamanan. Karena PIN ini memberikan akses ke "jeroan" mesin (kalibrasi, manual control), penyimpanannya harus aman namun mudah divalidasi.

Berdasarkan arsitektur **Laravel 12 + PostgreSQL** yang kita gunakan, berikut adalah rekomendasi teknis terbaik untuk penyimpanan PIN tersebut.

### **Rekomendasi Utama: Tabel Khusus `maintenance_sessions`**

Jangan simpan PIN ini di tabel `rvm_machines` sebagai kolom statis. Mengapa? Karena kita butuh *audit trail* (siapa yang minta, kapan, dan untuk mesin mana) serta manajemen kadaluarsa (*expiration*).

Sebaiknya buat tabel baru bernama **`maintenance_sessions`** atau **`rvm_access_tokens`**.

#### **1. Skema Database (Migrasi Laravel)**

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

#### **2. Alur Kerja Penyimpanan & Validasi**

Berikut adalah detail teknis bagaimana data mengalir:

**A. Saat Generate PIN (Di Server/Dashboard Admin)**
1.  Admin/Teknisi menekan tombol "Generate Maintenance PIN" untuk RVM-001.
2.  Laravel men-generate angka acak 6 digit (misal: `882910`).
3.  Laravel melakukan **Hashing** terhadap angka tersebut (sama seperti password): `Hash::make('882910')`.
4.  Simpan **Hash** tersebut ke tabel `maintenance_sessions` bersama `expires_at` (misal `now()->addHour()`).
5.  **Tampilkan PIN `882910` (Plain Text) hanya SATU KALI** ke layar Admin/Teknisi. Server tidak menyimpannya lagi.

**B. Saat Input PIN (Di Layar RVM-Edge)**
1.  Teknisi memasukkan `882910` di layar sentuh RVM.
2.  RVM mengirim request ke API: 
    *   `POST /api/v1/edge/maintenance/login`
    *   Body: `{ "pin": "882910", "device_id": "UUID_MESIN" }`
3.  **Backend Laravel melakukan pengecekan:**
    *   Cari sesi di tabel `maintenance_sessions` berdasarkan `rvm_machine_id` yang `used_at` masih NULL dan `expires_at` belum lewat.
    *   Lakukan verifikasi hash: `Hash::check($request->pin, $session->pin_hash)`.
4.  **Hasil:**
    *   Jika cocok: Update kolom `used_at` dengan waktu sekarang (supaya PIN tidak bisa dipakai lagi di device lain atau sesi lain), lalu kirim respons `200 OK` + Token Sesi Maintenance.
    *   Jika gagal: Kirim `401 Unauthorized` + Catat percobaan gagal di log keamanan.

---

### **Alternatif: TOTP (Time-based One-Time Password)**
*Jika Anda ingin RVM bisa masuk mode maintenance **tanpa koneksi internet (Offline)**.*

Jika lokasi RVM sering putus koneksi internet, metode database di atas tidak akan jalan (karena RVM tidak bisa tanya ke server apakah PIN valid).

**Solusinya:** Gunakan konsep seperti **Google Authenticator**.
1.  Di tabel `rvm_machines`, tambahkan kolom `maintenance_secret_key` (dienkripsi).
2.  Teknisi punya aplikasi khusus (bisa di menu "Tenant App" atau app authenticator biasa) yang men-generate kode 6 digit berdasarkan waktu saat ini dan Secret Key mesin tersebut.
3.  RVM-Edge (Python) juga punya Secret Key yang sama di config lokalnya.
4.  RVM memvalidasi PIN secara lokal (Offline) menggunakan algoritma TOTP.

**Kesimpulan:**
Untuk **MyRVM v2.1** yang mengandalkan konektivitas dan monitoring real-time, **Opsi 1 (Tabel Database `maintenance_sessions`)** adalah yang terbaik karena memberikan kontrol terpusat, bisa dicabut (revoke) kapan saja oleh Admin, dan memiliki log audit yang lengkap.