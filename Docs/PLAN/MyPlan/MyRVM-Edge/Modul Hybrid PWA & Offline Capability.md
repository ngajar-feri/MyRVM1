**Project Plan & Technical Specification** untuk modul **Hybrid PWA + Offline Guest Mode**.

Dokumen ini dirancang agar bisa langsung diserahkan kepada tim pengembang (Backend & Edge Engineer) sebagai panduan implementasi teknis pada Project MyRVM-Edge.

---

# Project Plan: Module Hybrid PWA & Offline Capability
**Project:** MyRVM v2.1 (Smart Reverse Vending Machine)
**Module Code:** `MOD-OFFLINE-01`
**Date:** 2026-01-27
**Status:** Approved for Implementation

---

## 1. Executive Summary
Modul ini bertujuan untuk menghilangkan *Single Point of Failure* pada arsitektur *Server-Driven UI* di mana putusnya koneksi internet menyebabkan antarmuka Kiosk menjadi *blank* atau tidak responsif.

Solusi yang diterapkan adalah pendekatan **Hybrid PWA**:
1.  **Anti-Blank:** Menggunakan Service Worker untuk menyajikan UI dari cache lokal browser saat offline.
2.  **Business Continuity:** Mengaktifkan **"Guest Mode / Donasi"** secara otomatis saat offline, menggunakan komunikasi WebSocket Lokal ke Python Edge.
3.  **Data Integrity:** Menerapkan mekanisme *Store & Forward* (Simpan Lokal & Sinkronisasi Susulan) untuk data transaksi offline.

---

## 2. Technical Architecture Change

### A. Communication Topology
Sistem akan memiliki dua jalur komunikasi yang berpindah secara otomatis (Failover):

1.  **Primary Channel (Online Mode):**
    *   `UI (Vue)` ↔ `WSS (Laravel Reverb)` ↔ `Server (Laravel)` ↔ `WSS (Reverb)` ↔ `Edge (Python)`
    *   *Fitur:* Login QR, Poin User, Voucher, Full Telemetry.

2.  **Failover Channel (Offline Mode):**
    *   `UI (Vue)` ↔ `WS (Localhost:8888)` ↔ `Edge (Python)`
    *   *Fitur:* Guest Mode (Donasi) Only. No Login.

### B. Stack Addition
*   **Frontend:** `vite-plugin-pwa` (Laravel), `workbox` (Cache Strategy).
*   **Edge (Python):** `websockets` library (Asyncio Local Server), `SQLite` (Local Buffer).

---

## 3. Implementation Phases

### Phase 1: Frontend PWA Implementation (Laravel & Vue)
**Goal:** UI Kiosk tetap bisa dimuat (tidak blank) meskipun kabel LAN dicabut.

*   **Task 1.1: Install & Config PWA**
    *   Install `vite-plugin-pwa`.
    *   Konfigurasi `vite.config.js`:
        *   Strategy: `NetworkFirst` untuk URL Kiosk (`/rvm-ui/*`). Artinya: Coba ambil dari internet, jika gagal, ambil dari cache terakhir.
        *   Strategy: `StaleWhileRevalidate` untuk assets (JS, CSS, Images).
*   **Task 1.2: Service Worker Logic**
    *   Generate `service-worker.js` yang menangani caching halaman HTML Kiosk.
    *   Pastikan Signed URL query string tidak merusak mekanisme caching (gunakan `ignoreURLParametersMatching` jika perlu).
*   **Task 1.3: Connection State Management (Pinia Store)**
    *   Buat `stores/connection.js`.
    *   Logic deteksi:
        ```javascript
        // Listen to Laravel Echo/Reverb disconnection
        window.Echo.connector.socket.on('disconnect', () => {
            store.setOfflineMode(); // Trigger UI change
            localSocket.connect();  // Connect to localhost:8888
        });
        ```

### Phase 2: Edge Local Bridge (Python)
**Goal:** Python Edge bisa menerima perintah dari UI Kiosk tanpa melalui Server Pusat.

*   **Task 2.1: Local WebSocket Server**
    *   Buat script baru `services/local_bridge.py`.
    *   Jalankan WebSocket Server di port `8888` (bound to `127.0.0.1` only demi keamanan).
*   **Task 2.2: Message Router**
    *   Implementasikan logic routing sederhana.
    *   Terima JSON: `{"command": "open_door", "session_type": "guest_offline"}`.
    *   Teruskan ke Hardware Controller (GPIO).

### Phase 3: Store & Forward Mechanism (Python Edge)
**Goal:** Menyimpan data botol saat offline dan mengirimnya saat online.

*   **Task 3.1: Local Database (SQLite)**
    *   Buat database lokal `rvm_local.db`.
    *   Table: `offline_transactions` (`id`, `timestamp`, `bottle_count`, `details_json`, `synced_at`).
*   **Task 3.2: Write Logic (Store)**
    *   Update script validasi botol (`detector.py`).
    *   IF `offline_mode == True`: Insert data ke SQLite alih-alih kirim ke API Telemetry.
*   **Task 3.3: Sync Service (Forward)**
    *   Buat thread background `sync_worker.py`.
    *   Logic:
        1.  Check Internet Connection (Ping Google/Server).
        2.  IF Online AND `offline_transactions` is NOT EMPTY:
        3.  Select all pending data.
        4.  POST to `/api/v1/edge/sync-offline`.
        5.  IF Success (200 OK) -> Delete from SQLite.

### Phase 4: Backend Sync Endpoint (Laravel)
**Goal:** Menerima data "rapelan" dari Edge.

*   **Task 4.1: API Endpoint**
    *   Route: `POST /api/v1/edge/sync-offline`.
    *   Middleware: `ValidateRvmApiKey`.
*   **Task 4.2: Controller Logic**
    *   Terima array transaksi.
    *   Looping insert ke tabel `transactions`.
    *   **Penting:** Set `user_id` ke `NULL` atau ID Akun Donasi Sistem (misal: ID 1 atau ID khusus).
    *   Set `type` transaksi sebagai `GUEST_DONATION`.

---

## 4. User Experience (UX) Flow

1.  **Normal (Online):**
    *   Layar menampilkan QR Code Sesi.
    *   User scan HP -> Login -> Masukkan Botol -> Poin Masuk Akun.

2.  **Disruption (Internet Lost):**
    *   Layar berkedip sebentar (Vue State Change).
    *   **Overlay Merah/Kuning:** *"Koneksi Server Terputus. Mode Darurat Aktif."*
    *   QR Code hilang. Tombol besar muncul: **"MULAI DONASI (Tanpa Poin)"**.

3.  **Guest Transaction:**
    *   User klik "MULAI DONASI".
    *   Pintu terbuka (Via Local Socket).
    *   User masukkan botol.
    *   Layar menampilkan: *"Terima kasih! 5 Botol didonasikan."* (Data masuk SQLite).

4.  **Recovery (Internet Back):**
    *   Layar kembali menampilkan QR Code.
    *   Background process mengirim data donasi ke server.

---

## 5. Security & Constraints

1.  **Local Socket Security:** WebSocket Lokal (`ws://localhost:8888`) hanya boleh di-bind ke `localhost`. Jangan bind ke `0.0.0.0` agar tidak bisa diakses dari jaringan luar (HP User/Hacker).
2.  **No Fraud:** Karena mode offline tidak ada validasi user, **DILARANG KERAS** menyimpan klaim poin user secara offline. Semua transaksi offline otomatis menjadi Donasi/Guest. Ini mencegah user memanipulasi saldo saat mesin offline.
3.  **Data Persistence:** Gunakan SQLite, bukan file JSON, untuk menghindari *data corruption* jika mesin mati listrik mendadak saat proses penulisan data.

---

## 6. Development Checklist

- [ ] (FE) Setup `vite-plugin-pwa` di Repository `MyRVM-Server`.
- [ ] (FE) Implementasi `OfflineStatus.vue` component.
- [ ] (FE) Implementasi Logic switch ke Local Websocket di `connectionStore`.
- [ ] (Edge) Install library `websockets` & `sqlite3` di environment Python Jetson.
- [ ] (Edge) Buat service `local_bridge.py`.
- [ ] (Edge) Implementasi logic SQLite insert di `detector.py`.
- [ ] (Edge) Implementasi `sync_worker.py`.
- [ ] (BE) Buat API Endpoint `/sync-offline`.
- [ ] **TESTING:** Cabut kabel LAN saat transaksi sedang berlangsung.

---
**Approved By:** ____________________ (Project Manager)
**Target Delivery:** Sprint 3 (MyRVM v2.1)