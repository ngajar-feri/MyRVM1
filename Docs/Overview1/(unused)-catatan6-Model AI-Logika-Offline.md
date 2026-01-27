Ini adalah analisis dan rencana implementasi teknis yang diperbarui berdasarkan koreksi Anda mengenai sumber Model AI, Logika Validasi Botol, dan konfirmasi strategi *Offline Guest Mode*.

---

### 1. Koreksi & Update Mental Model

Saya telah memperbarui "Mental Map" arsitektur sistem dengan poin-poin berikut:

#### **A. Distribusi Model AI (Hybrid Source)**
*   **Status:** Fleksibel.
*   **Sumber Valid:** Github Releases (Primary/Versioning), MinIO (Internal Dashboard), atau Upload Manual (HuggingFace/Colab).
*   **Implikasi Teknis:** Script Python di Edge (`updater.py`) tidak boleh hanya hardcoded ke MinIO. Ia perlu memiliki konfigurasi prioritas (Configurable Source). Kita membutuhkan file `manifest.json` atau endpoint API di server yang memberi tahu Edge: *"Versi terbaru adalah v2.1, download link-nya ada di [URL Github/MinIO/Lainnya]"*.

#### **B. Logika Validasi Botol (Strict Ruleset)**
Logika ini harus diimplementasikan di **Level Edge (Python)** untuk kecepatan (low latency rejection) dan konsistensi saat offline.

```python
# Pseudo-code Logic di RVM-Edge
def validate_detection(detections):
    classes = [d.class_name for d in detections]
    
    has_mineral = "mineral" in classes
    has_not_empty = "not_empty" in classes
    
    # Daftar blacklist
    forbidden = {"soda", "milk", "yogurt", "dishwasher", "non_mineral"}
    has_forbidden = any(item in classes for item in forbidden)

    # LOGIKA UTAMA
    if has_forbidden:
        return REJECT("Salah Jenis Material")
        
    if has_mineral and has_not_empty:
        return REJECT("Botol berisi cairan")
        
    if has_mineral and not has_not_empty:
        return ACCEPT("Botol PET Kosong Diterima")
        
    return IGNORE("Objek tidak dikenal")
```

---

### 2. Validasi Business Logic: "Guest Mode / Donasi"

Saya telah memeriksa kembali artefak dokumen, khususnya **`MyRVM-v2-Dokumentasi2.md`** pada bagian **"3.2. Alur untuk Pengguna Tamu (Donasi)"**.

*   **Temuan Dokumen:** Disebutkan bahwa tombol *"Lanjutkan sebagai Tamu (Donasi)"* memicu endpoint `/api/v2/rvm/session/activate-guest`. Saldo dialokasikan ke akun donasi sistem.
*   **Konteks Offline (Store & Forward):**
    Karena validasi saldo User atau QR User membutuhkan koneksi Database Server (untuk mencegah fraud/double claim), maka saat **OFFLINE**, mesin **HANYA BOLEH** beroperasi dalam **Mode Tamu/Donasi**.
    *   *Alasan:* Kita tidak bisa memverifikasi User ID saat internet mati. Namun, kita bisa menerima botol "tanpa tuan" (anonymous) yang nilainya nanti disinkronkan ke akun "System Donation" saat internet kembali menyala.

---

### 3. Blueprint Implementasi: OPSI A (Hybrid PWA + Offline Guest Mode)

Berikut adalah rencana teknis detail untuk mencegah layar *blank* dan memungkinkan transaksi donasi saat jaringan putus, menggunakan arsitektur **Laravel 12 (Server-Driven)** yang diperkuat PWA.

#### **Komponen Kunci:**
1.  **Frontend (Vue.js/Kiosk):** Service Worker + Pinia State Management.
2.  **Edge (Python):** Local WebSocket Server (selain Client ke Server Pusat).

#### **Langkah 1: Dual-Channel Communication Strategy**
Aplikasi Kiosk (Vue) harus memiliki dua jalur komunikasi.

*   **Jalur Utama (Online):** Vue -> Laravel Reverb (WebSocket Cloud) -> Python Edge.
*   **Jalur Darurat (Offline):** Vue -> `localhost:8888` (WebSocket Lokal di Jetson) -> Python Edge.

**Logic di Vue (Store):**
```javascript
// connectionStore.js
state: {
    isOnline: true,
    mode: 'standby' // 'scan_qr', 'guest_session', 'offline_guest'
}

actions: {
    checkHeartbeat() {
        // Ping Laravel Reverb
        if (reverb_disconnected) {
            this.isOnline = false;
            this.switchToLocalSocket(); // Connect ke localhost:8888
            this.mode = 'offline_guest'; // Paksa masuk mode donasi
            this.showOfflineBanner(); // "Mode Offline: Hanya Menerima Donasi"
        }
    }
}
```

#### **Langkah 2: Konfigurasi Service Worker (PWA)**
Di Laravel, gunakan paket `vite-plugin-pwa`.

**`vite.config.js`:**
Konfigurasikan strategi caching *NetworkFirst*.
```javascript
workbox: {
    runtimeCaching: [
        {
            // Cache halaman utama Kiosk UI
            urlPattern: ({ url }) => url.pathname.startsWith('/rvm-ui/'),
            handler: 'NetworkFirst', // Coba internet dulu, kalau mati ambil cache
            options: {
                cacheName: 'kiosk-ui-cache',
                expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 30 } // 30 hari
            }
        },
        // Cache assets (JS/CSS/Fonts)
        {
            urlPattern: /\.(?:js|css|png|jpg|svg)$/,
            handler: 'StaleWhileRevalidate',
        }
    ]
}
```
*Hasil:* Saat internet mati dan mesin restart, browser tetap bisa memuat UI dari cache lokal tanpa menampilkan "Dino Game" atau Blank Page.

#### **Langkah 3: Logika "Store & Forward" di Python Edge**

Script Python di Jetson adalah "Source of Truth" saat offline.

1.  **Event:** Internet Mati.
2.  **UI:** Vue beralih ke local socket, menampilkan "Mode Donasi".
3.  **Action:** User memasukkan botol.
4.  **Python Logic:**
    *   Mendeteksi botol (YOLO+SAM).
    *   Validasi (Logic Mineral vs Non-Empty).
    *   **Penyimpanan:** Karena API Server tidak bisa dihubungi, simpan data ke **SQLite Lokal** atau **JSON File** (`pending_transactions.json`).
        ```json
        [
          {
            "timestamp": "2026-01-26T10:00:00",
            "type": "guest_donation",
            "items": [
              {"class": "mineral", "confidence": 0.98},
              {"class": "mineral", "confidence": 0.95}
            ]
          }
        ]
        ```
5.  **Sync (Forward):**
    *   Thread terpisah di Python (`sync_manager.py`) terus melakukan *ping* ke Server.
    *   Saat Server Online: Baca `pending_transactions.json` -> Upload Bulk ke API `/api/v1/edge/sync-offline-transactions` -> Kosongkan file lokal.

#### **Langkah 4: User Experience (UX) saat Jitter/Down**

1.  **Skenario:** Internet putus tiba-tiba saat user sedang scan QR.
2.  **Reaksi Sistem:**
    *   WebSocket Reverb putus (`disconnect` event).
    *   Vue mendeteksi putus -> Otomatis ganti tampilan.
    *   **Pesan Layar:** *"Maaf, koneksi server terputus. Beralih ke Mode Donasi. Botol yang dimasukkan akan didonasikan."*
    *   QR Code Login **disembunyikan** (karena tidak bisa validasi login).
    *   Pintu RVM tetap bisa terbuka jika user menekan "Lanjut Donasi".

### Kesimpulan Rencana

Dengan skema ini, Anda menjawab tantangan **Critical Observation** saya:

1.  **Anti-Blank:** PWA Service Worker menjamin UI selalu ter-load dari cache lokal Jetson (browser cache).
2.  **Fungsionalitas:** Hybrid Communication (Cloud Socket + Local Socket) menjamin mesin tetap bisa diperintah oleh UI.
3.  **Bisnis:** Mode Donasi (Guest) menjadi *fallback safe-haven* saat offline, sehingga potensi botol masuk tetap tertangkap (Store & Forward) tanpa risiko keamanan akun user.

Apakah blueprint arsitektur offline ini sudah sesuai dengan ekspektasi Anda untuk dieksekusi?