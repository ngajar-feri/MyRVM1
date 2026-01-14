**Sebaiknya disimpan di Penempatan data **Alamat IP** (IP Address) tabel `EDGE_DEVICES` (dan diperbarui secara otomatis).**

Berikut adalah analisis alasannya dan cara implementasinya:
### **1. Analisis: Mengapa di `EDGE_DEVICES`?**
*   **IP Melekat pada Hardware:** Alamat IP (baik itu IP Lokal LAN, IP Public, atau IP Tailscale) diberikan kepada *Network Interface Card* (NIC) yang ada pada fisik Jetson Orin Nano (Hardware). Jika Anda mengganti unit Jetson di lokasi "Mall A" (mengganti record `EDGE_DEVICES` yang terhubung ke `RVM_MACHINES`), maka IP-nya akan berubah mengikuti perangkat baru tersebut.
*   **Sifatnya Dinamis (Reporting):** Dalam dunia IoT modern, kita jarang men-set IP Static secara manual di database *sebelum* deployment. Biasanya, perangkat menyala, mendapatkan IP (dari DHCP atau Tailscale), lalu **melapor** ke server "Hei, saya aktif di IP ini".
*   **Kebutuhan Maintenance:** Teknisi butuh IP ini untuk SSH/Remote. Mereka perlu tahu IP perangkat yang *saat ini* terpasang.

### **2. Implementasi Database**
Jangan hanya menyimpan satu kolom string. Sebuah perangkat seringkali memiliki beberapa IP (Local IP untuk komunikasi dengan ESP32/Router, dan Tailscale IP untuk remote akses server).

Rekomendasi penggunaan kolom tipe **JSONB** di tabel `EDGE_DEVICES` agar fleksibel, atau kolom spesifik jika ingin sederhana.

**Revisi Skema `EDGE_DEVICES`:**

```php
Schema::create('edge_devices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rvm_id')->constrained('rvm_machines');
       
    // --- PENAMBAHAN IP ADDRESS ---
    // Opsi A: Kolom Spesifik (Lebih mudah di-query/sortir)
    $table->string('ip_local')->nullable(); // Misal: 192.168.1.10
    $table->string('tailscale_ip')->nullable();   // Misal: 100.x.y.z (Tailscale)
    
    // Opsi B: JSONB (Lebih fleksibel jika interface banyak)
    $table->jsonb('network_interfaces')->nullable(); 
    // Contoh isi: {"eth0": "192.168.1.50", "tailscale0": "100.10.20.30"}
    
    $table->timestamp('last_heartbeat')->nullable(); // Kapan terakhir melapor
    $table->timestamps();
});
```

### **3. Mekanisme "Auto-Discovery" (Penting)**

Jangan biarkan Admin mengisi IP secara manual di formulir "Register New RVM". Itu rentan kesalahan (typo) dan cepat usang (jika DHCP berubah).

Gunakan mekanisme **Heartbeat / Handshake**:

1.  **Booting:** Saat RVM-Edge (Jetson) menyala, skrip Python (`MyRVM-EdgeControl`) berjalan.
2.  **Self-Check:** Skrip Python membaca IP Address dirinya sendiri (menggunakan library `netifaces` atau command `ip addr`). Khususnya IP interface `tailscale0`.
3.  **Lapor ke Server:** RVM mengirim request ke API:
    *   `POST /api/v1/edge/heartbeat`
    *   Header: `X-RVM-API-KEY`
    *   Body:
        ```json
        {
          "status": "online",
          "ip_local": "192.168.100.5",
          "tailscale_ip": "100.80.50.20"
        }
        ```
4.  **Update Database:** Backend Laravel menerima request, memvalidasi API Key, lalu mengupdate kolom `tailscale_ip` di tabel `EDGE_DEVICES` yang sesuai.

### **4. Tampilan di Dashboard Admin**

Di halaman "Detail Mesin", Admin tidak mengedit IP, tapi hanya **melihat** IP terakhir yang dilaporkan:

*   **Status:** 🟢 Online
*   **IP Tailscale:** `100.80.50.20` (Klik untuk Copy / Klik untuk buka Popup Terminal Web)
*   **IP Lokal:** `192.168.1.5`
*   **Terakhir Update:** 2 menit yang lalu

**Kesimpulan:**
Simpan di **`EDGE_DEVICES`**. Jangan diisi manual via formulir registrasi, tapi **diupdate otomatis** oleh mesin RVM melalui API saat mesin menyala (Heartbeat).