Berikut adalah **Project Plan: MyRVM-Server (Module: RVM-UI Kiosk Interface) Versi 1.2**, yang telah diperbarui secara komprehensif.

Revisi ini mencakup penambahan fitur **Mode Tampilan Adaptif (Light/Dark Mode)** yang responsif terhadap waktu lokal perangkat, spesifikasi palet warna yang detail, penyesuaian pada fitur **Log Viewer** untuk isolasi data per mesin, serta integrasi konfigurasi zona waktu pada proses instalasi.

---

# Project Plan: MyRVM-Server (Module: RVM-UI Kiosk Interface)

**Version:** 1.2
**Parent Repository:** `MyRVM-Server`
**Target Environment:** Chromium Browser (Kiosk Mode) pada Jetson Orin Nano.
**Role:** Menyajikan antarmuka visual ke layar sentuh dan menjembatani interaksi User/Teknisi ke Backend.

---

## 1. Executive Summary

**RVM-UI Kiosk** adalah modul frontend yang disajikan oleh server (`server-side rendering` awal dengan Blade, dilanjutkan dengan `client-side interactivity` dengan Vue.js). Modul ini dirancang untuk dijalankan pada layar sentuh mesin RVM.

Modul ini tidak berkomunikasi langsung dengan hardware mesin. Sebaliknya, ia bertindak sebagai klien yang mengirimkan instruksi ke **MyRVM-Server** via API. Server kemudian meneruskan instruksi tersebut ke **MyRVM-Edge** (Python) melalui WebSocket.

Fitur utama dalam versi ini adalah dukungan **Adaptive Theme** (Light/Dark Mode) yang dapat diatur secara manual atau otomatis mengikuti waktu lokal mesin RVM, serta panel maintenance yang lebih terisolasi untuk keamanan data operasional.

---

## 2. Technical Architecture

### **Stack Teknologi**
*   **Backend Serving:** Laravel 12 (Blade).
*   **Frontend Logic:** Vue.js 3 (Composition API).
*   **Styling:** Tailwind CSS (Custom Config untuk Kiosk/Dark/Light Mode).
*   **Real-time:** Laravel Echo + Reverb (WebSocket).
*   **State Management:** Pinia (untuk mengelola Theme State & Machine Config).

### **Data Flow Architecture**
1.  **Request (Kiosk UI):** User/Teknisi menyentuh tombol di layar. Vue.js memanggil API Server.
2.  **Process (Server):** Laravel memvalidasi request dan otorisasi sesi kiosk.
3.  **Broadcast (Server):** Laravel memancarkan event WebSocket.
4.  **Execute (Edge):** Python Daemon di mesin menerima event dan mengeksekusi perintah hardware.
5.  **Feedback (Edge -> Server -> UI):** Python mengirim status terbaru ke API, Server mem-broadcast update ke UI Kiosk.

---

## 3. URL & API Endpoints Specification

### **A. Web Routes (Browser Entry Point)**
*   **URL:** `GET /rvm-ui/{machine_uuid}`
*   **Controller:** `App\Http\Controllers\Dashboard\KioskController@index`
*   **Fungsi:**
    *   Memvalidasi `machine_uuid`.
    *   Merender view Blade dasar.
    *   **Inject Config:** Mengirimkan konfigurasi awal ke Vue (termasuk Timezone mesin dan preferensi tema default).

### **B. API Endpoints (Consumed by Kiosk Vue App)**
**Group Prefix:** `/api/v1/kiosk`

| Method | Endpoint | Controller | Deskripsi Fungsi |
| :--- | :--- | :--- | :--- |
| **GET** | `/session/token` | `SessionController@getToken` | Request token sesi QR baru. |
| **POST** | `/session/guest` | `SessionController@activateGuest` | Aktivasi mode tamu. |
| **POST** | `/auth/pin` | `AuthController@verifyPin` | Validasi PIN Teknisi. |
| **POST** | `/maintenance/command` | `MaintenanceController@sendCommand` | Kirim perintah hardware. |
| **GET** | `/logs` | `LogController@index` | Mengambil log aktivitas **spesifik hanya untuk mesin ini**. |
| **POST** | `/config/theme` | `ConfigController@updateTheme` | Menyimpan preferensi tema (Auto/Manual) untuk mesin ini. |

---

## 4. UI/UX Specifications

### **4.1. Theme & Visual System (Adaptive)**

Sistem UI mendukung mode **Light** dan **Dark** yang dapat berubah otomatis berdasarkan waktu lokal mesin (jam dari Jetson Orin Nano).

#### **A. Palet Warna Light Mode (Natural & Organic)**
| Nama Warna (ID) | Kode Hex | Deskripsi & Tujuan Filosofis |
| :--- | :--- | :--- |
| **Latar Belakang Utama** | `#FDFDFD` | **Putih Alami.** Sedikit lebih lembut dari putih murni untuk mengurangi silau layar dan memberikan kesan kertas alami. |
| **Latar Belakang Sekunder** | `#EEEEEE` | **Abu-abu Sangat Terang.** Ideal untuk navigasi samping atau kontainer konten yang lembut, memberikan kedalaman tanpa mengganggu fokus. |
| **Teks Utama** | `#404040` | **Abu-abu Arang Hangat.** Kontras yang sangat baik dan nyaman dibaca, terasa lebih organik daripada hitam pekat. |
| **Teks Sekunder** | `#9E9E9E` | **Abu-abu Sedang Netral.** Untuk informasi tambahan, tanggal, atau ikon yang tidak memerlukan penekanan kuat. |
| **Aksen Utama** | `#4CAF50` | **Hijau Alam (Nature Green).** Diasosiasikan dengan kesehatan, pertumbuhan, dan keseimbangan. Aksen yang menenangkan dan positif. |
| **Aksen Peringatan** | `#FFB74D` | **Oranye Lembut.** Notifikasi atau status peringatan yang menarik perhatian secara lembut, tidak mengancam. |

#### **B. Palet Warna Dark Mode (Low Light & Energy Saving)**
*   **Latar Belakang:** `#1A1A1A` (Deep Charcoal) - Mengurangi emisi cahaya biru di malam hari.
*   **Teks Utama:** `#E0E0E0` (Soft White).
*   **Aksen:** Tetap menggunakan Hijau Alam namun dengan *luminance* yang disesuaikan agar tetap terlihat jelas di latar gelap.

### **4.2. Configuration Feature (Setup Wizard Integration)**
Pada tahap instalasi awal (Setup Wizard di RVM-Edge), ditambahkan langkah konfigurasi waktu:
*   **Time Zone Configuration:**
    *   **Manual:** Dropdown untuk memilih zona waktu (misal: `Asia/Jakarta`).
    *   **Automatic:** Sinkronisasi via NTP (Internet Time) saat terkoneksi.
*   Data zona waktu ini dikirim ke server saat **Handshake** dan disimpan di database `edge_devices`.
*   UI Kiosk menggunakan data ini untuk menentukan kapan harus beralih mode (misal: Auto Dark Mode pukul 18:00 - 06:00 waktu lokal mesin).

### **4.3. Public Interface (Default State)**
*   **Komponen:** `IdleScreen.vue`, `SessionScreen.vue`.
*   **Fitur:** QR Code Dinamis, WebSocket Listener, Visual Feedback Animasi.

### **4.4. Maintenance Interface (Technician Flow)**
*   **Access Trigger:** Tombol tersembunyi / Gesture.
*   **Pin Pad:** Virtual NumPad untuk login.
*   **Control Panel:**
    *   **Theme Toggle:** Switch manual (Light/Dark/Auto).
    *   **Log Viewer (Revisi):**
        *   Jendela scrollable menampilkan 20 log terakhir.
        *   **Filtering:** Backend **wajib** memfilter query log `WHERE rvm_machine_id = {current_machine_id}`.
        *   Teknisi di Mesin A **tidak boleh** melihat log error dari Mesin B atau aktivitas modul User lain. Ini memastikan privasi dan relevansi data saat debugging di lapangan.

---

## 5. Directory Structure Mapping

```text
MyRVM-Server/
├── app/
│   ├── Http/Controllers/
│   │   ├── Dashboard/KioskController.php      # Render Halaman & Inject Config
│   │   ├── Api/Kiosk/                         # API Khusus Kiosk
│   │       ├── LogController.php              # Log Viewer (Scoped by Machine ID)
│   │       ├── ConfigController.php           # Theme Config
│   │       └── ... (Session, Auth, Maintenance)
├── resources/
│   ├── css/
│   │   └── kiosk.css                          # Tailwind Custom Config (Colors)
│   ├── js/
│   │   ├── kiosk/
│   │   │   ├── stores/themeStore.js           # Pinia: Manage Light/Dark Logic
│   │   │   ├── components/
│   │   │   │   ├── Maintenance/LogViewer.vue  # Component Log Viewer
│   │   │   │   └── ...
```

---

## 6. Implementation Roadmap

### **Phase 1: Backend Routes & Controller Setup**
*   Setup Controller & Routes.
*   **[Update]** Implementasi logika filtering log berdasarkan `machine_uuid` di `LogController`.

### **Phase 2: Frontend Foundation & Theming**
*   Setup Vite & Tailwind.
*   **[Update]** Konfigurasi Tailwind `tailwind.config.js` dengan palet warna kustom (Light & Dark).
*   **[Update]** Implementasi `themeStore` di Pinia untuk menangani logika perpindahan tema otomatis berdasarkan jam.

### **Phase 3: Component Development**
*   Develop `IdleScreen`, `PinPad`.
*   **[Update]** Develop `MaintenancePanel` dengan fitur Toggle Theme dan Log Viewer yang sudah terisolasi datanya.

### **Phase 4: Integration**
*   Integrasi data zona waktu dari database ke frontend Vue.

---

## 7. Change Log (History Perubahan)

Berikut adalah ringkasan evolusi dokumen dari Versi 1.0 ke 1.2:

| Versi | Perubahan Utama | Detail |
| :--- | :--- | :--- |
| **1.0** | **Inisiasi Dokumen** | - Mendefinisikan konsep dasar Kiosk Mode.<br>- Menetapkan arsitektur *Server-Driven UI* (Laravel menyajikan Blade/Vue ke Edge).<br>- Menetapkan alur komunikasi WebSocket untuk kontrol hardware. |
| **1.1** | **Spesifikasi API & URL** | - Menambahkan detail rute Web (`/rvm-ui/{uuid}`) dan API Endpoints (`/api/v1/kiosk/*`).<br>- Memperjelas pemisahan antara rute browser dan rute API data.<br>- Memetakan struktur direktori backend yang diperlukan. |
| **1.2** | **Theming & Data Isolation** | - **UI/UX:** Menambahkan spesifikasi **Light Mode** dengan palet warna detail (Hex Code & Filosofi).<br>- **Fitur:** Menambahkan konfigurasi **Theme (Light/Dark/Auto)** yang berbasis waktu lokal mesin.<br>- **Setup:** Menambahkan kebutuhan konfigurasi **Time Zone** pada proses instalasi awal (Setup Wizard).<br>- **Keamanan/Data:** Merevisi **Log Viewer** pada Maintenance Interface agar **terisolasi (scoped)**, hanya menampilkan log milik mesin yang sedang diakses, bukan log global server. |