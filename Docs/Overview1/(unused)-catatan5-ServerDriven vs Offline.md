### STRATEGIC RESPONSE: Mengatasi Isu "Server-Driven UI vs Offline"

Anda bertanya bagaimana menyiasati agar layar tidak *blank/error* saat internet putus, mengingat UI dirender oleh Server (Blade). Ini adalah **Single Point of Failure** terbesar dalam arsitektur "Thin Client" untuk IoT.

Berikut adalah dua pendekatan objektif:

#### OPSI A: Mempertahankan Arsitektur Saat Ini (Hybrid Service Worker)
*Tetap render di Server, tapi tambahkan ketahanan di Browser.*

1.  **PWA Service Worker (Wajib):** Anda harus mengimplementasikan Service Worker (`service-worker.js`) pada Blade template Kiosk.
2.  **Cache Strategy (NetworkFirst, fallback to Cache):**
    *   Cache aset statis (CSS, JS, Logo).
    *   Cache satu halaman khusus: `offline_fallback.blade.php`.
3.  **Mekanisme:**
    *   Saat internet putus, browser gagal me-load URL Kiosk.
    *   Service Worker menangkap error tersebut dan menyajikan `offline_fallback.html` dari cache lokal browser.
    *   **Halaman Offline Cerdas:** Halaman offline ini bukan sekadar pesan "No Internet". Halaman ini harus berisi **Vue Component minimalis** yang bisa berkomunikasi langsung dengan Python Edge via **WebSocket Lokal (Localhost)** atau API Lokal Edge (jika dibuka).
    *   *Limitasi:* User mungkin tidak bisa login (scan QR Server), tapi mesin masih bisa menampilkan pesan "Sedang Gangguan Jaringan, Mohon Tunggu" atau bahkan menerima botol dalam mode "Store & Forward" (jika logika bisnis mengizinkan transaksi tanpa validasi server real-time).

**Pro:** Tidak perlu merombak arsitektur server-side.
**Con:** Kompleksitas di JavaScript/Service Worker. Transisi UX mungkin terasa "cegukan".

#### OPSI B: Berubah ke Edge-Side Rendering (Architectural Shift)
*Memindahkan rendering UI sepenuhnya ke Jetson.*

1.  **Cara Kerja:**
    *   Install Web Server ringan (Nginx/Lighttpd) atau Node.js langsung di Jetson Orin Nano.
    *   Aplikasi UI Kiosk (Vue.js build) ditaruh di storage lokal Jetson.
    *   Browser Kiosk membuka `http://localhost:8080`.
2.  **Komunikasi:**
    *   UI Lokal bicara ke Python Daemon via WebSocket Lokal/HTTP Lokal (sangat cepat, 0 latency).
    *   Python Daemon yang mengatur komunikasi ke Server Pusat (Upload transaksi, download profil user).
3.  **Mekanisme Offline:**
    *   Jika internet mati, UI **tetap menyala 100% normal** karena file-nya lokal.
    *   Python Daemon mendeteksi offline -> UI menampilkan status "Offline Mode" -> Transaksi disimpan lokal (Store) -> Sync saat online (Forward).

**Pro:** **UX Terbaik.** Kebal terhadap gangguan jaringan. Latency UI nol. Sangat robust untuk IoT.
**Con:** **Deployment Nightmare.** Jika ingin update tampilan (misal: ganti logo atau teks), Anda harus men-deploy kode baru ke *semua* mesin RVM (git pull di setiap mesin), tidak cukup hanya update di Server Pusat.

### REKOMENDASI SAYA (Strategic Choice)

Mengingat Anda menggunakan **Laravel 12** dan ingin sentralisasi kontrol yang kuat, saya menyarankan **OPSI A (Hybrid PWA)** dengan sedikit modifikasi:

1.  Gunakan **Laravel Vite PWA Plugin**. Konfigurasi agar aset kritis di-cache.
2.  Desain **"Offline UI State"** di Vue.js yang aktif otomatis saat koneksi WebSocket ke Laravel Reverb terputus.
    *   Ingat, Anda menggunakan WebSocket. Event `disconnect` di client WebSocket bisa mendeteksi putusnya koneksi lebih cepat daripada request HTTP.
    *   Saat `socket.on('disconnect')` terpanggil -> Vue App (yang sudah ter-load di memori browser) langsung *switch* ke tampilan "Offline Mode" tanpa perlu reload halaman.
3.  **Jangan refresh halaman.** Selama browser tidak di-refresh, Vue App masih hidup di RAM. Gunakan ini. Biarkan Vue menangani state koneksi secara reaktif.

**Action Plan Konkret:**
1.  Pastikan Vue App di Kiosk adalah **SPA (Single Page App)** atau **Inertia** yang di-load sekali di awal.
2.  Listen event WebSocket disconnect.
3.  Jika disconnect, overlay layar dengan komponen `OfflineStatus.vue`.
4.  Jika reload terjadi saat mati lampu/internet mati total: Barulah Service Worker masuk menyajikan halaman statis darurat.