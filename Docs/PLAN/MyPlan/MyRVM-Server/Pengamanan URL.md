Berikut adalah **Rencana Implementasi Modul Keamanan URL Kiosk** mencakup alur data, contoh kode, dan struktur JSON.

---

# Rencana Implementasi: Keamanan URL Kiosk (Signed UUID)

**Tujuan:** Mengamankan akses ke antarmuka layar sentuh (Kiosk) agar hanya mesin fisik yang valid yang dapat membukanya, menggunakan kombinasi UUID dan Tanda Tangan Digital (Signed URL) tanpa batas waktu.

---

## 1. Alur Kerja (Workflow)

1.  **Inisialisasi (Edge):** Saat booting, `MyRVM-Edge` (Python) melakukan Handshake.
2.  **Generasi URL (Server):** `MyRVM-Server` (Laravel) memvalidasi mesin, lalu membuat URL Kiosk khusus yang sudah ditandatangani.
3.  **Distribusi (Response):** Server mengirim URL tersebut kembali ke Edge.
4.  **Eksekusi (Edge):** Edge membuka browser menuju URL tersebut.
5.  **Verifikasi (Server):** Saat browser memuat halaman, Server memvalidasi tanda tangan URL sebelum menampilkan UI.
6.  **Penggunaan (Edge):** Kiosk hanya boleh diakses oleh mesin yang valid.
7.  **URL Final:** Bagaimana Bentuk URL Kiosk Akhirnya?
    *   Dengan menggabungkan UUID dan Signed URL, Laravel membuat hash SHA256 dari URL + APP_KEY. kemudian, URL yang akan dibuka oleh Chromium di mesin RVM (KIOSK-Mode) akan terlihat seperti ini:
        *   `https://myrvm.server/rvm-ui/550e8400-e29b-41d4-a716-446655440000?signature=7650a2b5e90d7c...`
    *   Segment 1: `/rvm-ui/` -> Route path.
    *   Segment 2: `550e8400-e29b-41d4-a716-446655440000` -> UUID Mesin. Ini yang digunakan controller untuk mencari record mesin di database `RvmMachine::where('uuid', $uuid)->first()`. 
    *   Segment 3: `?signature=7650a2b5e90d7c...` -> Signed Query String. Ini yang memvalidasi bahwa URL ini asli dari server dan belum dimodifikasi.

---

## 2. Struktur Data (JSON Payload & Response)

### **A. Request Handshake (Dari Edge ke Server)**
*   **Method:** `POST`
*   **Endpoint:** `/api/v1/edge/handshake`
*   **Header:** `X-RVM-API-KEY: [API_KEY_RAHASIA]`

**Body JSON:**
*(Data ini dikirim oleh Python script di Jetson)*
```json
    {
      // --- 1. IDENTITAS (Wajib) ---
      "hardware_id": "RVM-202601-006",  // Dari rvm-credentials.json
      "name": "RVM KU1",  // Dari rvm-credentials.json

      // --- 2. JARINGAN & LOKASI (Auto Detect) ---
      "ip_local": "192.168.1.105",
      "ip_vpn": "100.80.50.20",
      "timezone": "Asia/Jakarta",
      
      // --- 3. INFO PERANGKAT LUNAK (Auto Detect) ---
      "firmware_version": "v1.7.0",
      "controller_type": "NVIDIA Jetson Orin Nano",
      "ai_model_version": "YOLO11n-v1.0.0",
      
      // --- 4. HEALTH METRICS (Snapshot saat Handshake) ---
      "health_metrics": {
        "cpu_usage_percent": 15.5,
        "memory_usage_percent": 42.0,
        "disk_usage_percent": 12.8,
        "cpu_temperature": 45.0
      },

      // --- 5. KONFIGURASI HARDWARE (Gabungan Auto-Detect + Manual Calibration) ---
      // Masuk ke kolom JSONB 'hardware_config' di Database
      "config": {
        "cameras": [
          { "id": 0, "path": "/dev/video0", "model": "IMX219", "status": "ready" }
        ],
        // Sensor-sensor yang terhubung ke perangkat keras
        "sensors": [
          { 
            "name": "bin_capacity", 
            "type": "Ultrasonic HC-SR04", 
            "status": "calibrated",
            "max_distance_cm": 120 
          },
          {
            "name": "temperature_internal",
            "type": "DHT22",
            "status": "online"
          }
        ],
        // Actuator-actuator yang terhubung ke perangkat keras  
        "actuators": [
          { "name": "door_motor", "status": "ok" },
          { "name": "conveyor_motor", "status": "ok" }
        ],
        // Mikrokontroler yang terhubung ke perangkat keras
        "microcontroller": { "port": "/dev/ttyTHS1", "status": "connected" }
      },

      // --- 6. HASIL DIAGNOSTIK (Untuk Log Audit Server) ---
      "diagnostics": {
        "network_check": "pass",
        "camera_check": "pass",
        "motor_test": "pass",
        "ai_inference_test": "pass" // Tambahan: memastikan model AI bisa load
      }
    }
    ```
```

### **B. Response Handshake (Dari Server ke Edge)**
*   **Status:** `200 OK`

**Body JSON:**
*(Perhatikan bagian `kiosk.url`)*
```json
{
  "status": "success",
  "message": "Handshake successful. Configuration synced.",
  "data": {
    // 1. IDENTITAS LOGIS (Sinkronisasi dengan Database Server)
    "identity": {
      "rvm_id": 105,
      "rvm_uuid": "550e8400-e29b-41d4-a716-446655440000",
      "rvm_name": "RVM Mall Grand Indonesia - Lantai 1"
    },

    // 2. KIOSK UI CONFIGURATION (Untuk Browser)
    "kiosk": {
      // URL spesifik yang wajib dibuka oleh Chromium Browser (Mode Kiosk)
      // INI URL YANG SUDAH DI-SIGN
      "url": "https://myrvm.penelitian.my.id/rvm-ui/550e8400-e29b-41d4-a716-446655440000?signature=7650a2b5e90d7c...",
      
      // Timezone server untuk sinkronisasi jam tampilan dan log
      "timezone": "Asia/Jakarta" 
    },

    // 3. WEBSOCKET CONFIGURATION (Untuk Komunikasi Real-time)
    "websocket": {
      // Channel Privat khusus mesin ini untuk mendengarkan perintah (Buka Pintu, dll)
      "channel": "rvm.550e8400-e29b-41d4-a716-446655440000",
      
      // Token otorisasi sementara agar Python script boleh subscribe ke channel tersebut
      "auth_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIj...",
      
      // Config koneksi Reverb (opsional jika hardcoded di Edge, tapi lebih baik dinamis)
      "host": "myrvm.penelitian.my.id",
      "port": 443,
      "scheme": "wss"
    },

    // 4. OPERATIONAL POLICY (Kebijakan / Aturan Main)
    "policy": {
      // Jika true, mesin menolak transaksi user (hanya teknisi yang bisa akses)
      "is_maintenance_mode": false,
      
      // Batas sensor (Threshold) yang diatur Admin di Dashboard
      "bin_full_threshold_percent": 90,   // Jika sensor baca > 90%, set status FULL
      "camera_idle_timeout_sec": 60,      // Matikan kamera jika idle 60 detik
      "motor_speed_delay": 0.005          // Kecepatan motor stepper
    },

    // 5. AI MODEL VERSIONING (Untuk Auto Update)
    "ai_model": {
      // Versi yang seharusnya berjalan saat ini menurut Server
      "target_version": "v1.0.0",
      
      // Checksum SHA256 untuk verifikasi integritas file
      "hash": "a0e5156e1d53c249d4c859a4545e9505f87d6807c7113376a383da47fa45f241",
      
      // Sumber download (bisa GitHub API atau MinIO URL)
      "update_source_url": "https://api.github.com/repos/ngajar-feri/MyRVM1/releases/latest"
    }
  }
}
```

---

## 3. Implementasi Kode (Backend Laravel)

### **Langkah A: Persiapan Database & Route**

1.  **Migration:** Pastikan tabel `rvm_machines` punya kolom `uuid`.
    * perlu menambahkan kolom baru bernama uuid pada tabel rvm_machines jika belum ada.
    ```php
    $table->uuid('uuid')->unique()->after('id');
    ```
    * UUID di-generate otomatis setelah mendapat data dari MyRVM-Edge melalui endpoint POST `/api/v1/edge-device/handshake`.

2.  **Routes (`routes/web.php`):**
    Definisikan route untuk Kiosk dengan middleware `signed`.
    ```php
    use App\Http\Controllers\Dashboard\KioskController;

    Route::get('/rvm-ui/{uuid}', [KioskController::class, 'index'])
        ->name('kiosk.index')
        ->middleware('signed'); // Middleware bawaan Laravel untuk validasi signature
    ```

### **Langkah B: Controller Handshake (`EdgeDeviceController.php`)**

Di sinilah URL dibuat.

```php
use Illuminate\Support\Facades\URL;

public function handshake(Request $request)
{
    // 1. Validasi API Key (biasanya via Middleware auth.rvm)
    $rvmMachine = $request->user(); 

    // 2. Update data Hardware (seperti biasa...)
    $rvmMachine->edgeDevice->update([...]);

    // 3. GENERATE SIGNED URL (Permanen / Tanpa Waktu)
    // URL::signedRoute defaultnya tidak ada expiry jika parameter waktu tidak diisi
    $kioskUrl = URL::signedRoute('kiosk.index', [
        'uuid' => $rvmMachine->uuid
    ]);

    // 4. Return Response
    return response()->json([
        'status' => 'success',
        'data' => [
            'identity' => ['rvm_name' => $rvmMachine->name],
            'kiosk' => [
                'url' => $kioskUrl, // URL dikirim ke sini
                'timezone' => 'Asia/Jakarta'
            ],
            // ... data websocket dll
        ]
    ]);
}
```

### **Langkah C: Controller Kiosk (`KioskController.php`)**

Menangani request saat browser membuka URL.

```php
namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\RvmMachine;

class KioskController extends Controller
{
    public function index(Request $request, $uuid)
    {
        // Middleware 'signed' sudah memvalidasi signature sebelum masuk ke sini.
        // Jika signature salah/diubah, Laravel otomatis return 403 Forbidden.

        // Cari mesin berdasarkan UUID
        $machine = RvmMachine::where('uuid', $uuid)->firstOrFail();

        // Render View (Blade + Vue)
        return view('dashboard.kiosk.index', [
            'machine' => $machine,
            'kioskConfig' => [
                'uuid' => $machine->uuid,
                'name' => $machine->name,
                // Data lain untuk Vue
            ]
        ]);
    }
}
```

---

## 4. Contoh Implementasi Kode (Edge Python)

Bagaimana Python menggunakan URL tersebut.

```python
# main.py (Snippet)

def perform_handshake():
    # ... setup payload ...
    
    response = requests.post(SERVER_URL + "/api/v1/edge/handshake", json=payload, headers=headers)
    
    if response.status_code == 200:
        data = response.json()['data']
        
        # 1. Ambil URL Kiosk yang aman
        kiosk_url = data['kiosk']['url']
        print(f"🔗 Received Kiosk URL: {kiosk_url}")
        
        # 2. Simpan ke config.json (untuk backup jika restart tanpa internet)
        save_config({'ui_url': kiosk_url})
        
        # 3. Launch Browser
        launch_kiosk_browser(kiosk_url)
        
    else:
        print("❌ Handshake Failed")

def launch_kiosk_browser(url):
    # Command untuk membuka Chromium Fullscreen
    cmd = [
        "chromium-browser",
        "--kiosk",
        "--noerrdialogs",
        "--disable-infobars",
        url
    ]
    subprocess.Popen(cmd)
```

---

## 5. Skenario Keamanan

1.  **Skenario Sukses:**
    *   Python dapat URL: `.../rvm-ui/550e...?signature=abc`
    *   Browser buka URL tersebut.
    *   Server cek signature `abc` cocok dengan `550e...` + `APP_KEY`.
    *   **Hasil:** Halaman Kiosk terbuka.

2.  **Skenario Serangan (Ubah UUID):**
    *   Hacker ubah URL jadi: `.../rvm-ui/9999...?signature=abc`
    *   Server cek signature. Hash dari `9999...` tidak cocok dengan `abc`.
    *   **Hasil:** Server menolak dengan **403 Forbidden**.

3.  **Skenario Serangan (Hapus Signature):**
    *   Hacker buka: `.../rvm-ui/550e...` (tanpa signature).
    *   Middleware `signed` mendeteksi parameter hilang.
    *   **Hasil:** Server menolak dengan **403 Forbidden**.