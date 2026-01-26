# Implementation Plan: Kiosk URL Security (Revised)

## Deep Analysis Results

### 📊 Database Schema Status

| Table | Column | Status | Action Required |
|-------|--------|--------|-----------------|
| `rvm_machines` | `uuid` | ❌ **TIDAK ADA** | Migration baru untuk menambah kolom |
| `rvm_machines` | `serial_number` | ✅ Ada | Format: `RVM-YYYYMM-XXX` |
| `technician_assignments` | `access_pin` | ✅ Ada | PIN plaintext (6 digit) |
| `technician_assignments` | `pin_expires_at` | ✅ Ada | Expiry timestamp |

### 🐛 Code Inconsistencies Found

| File | Issue | Fix |
|------|-------|-----|
| `AuthController.php:104` | Uses `where('uuid', ...)` - **kolom tidak ada** | Ganti ke `where('uuid', ...)` setelah migration |
| `AuthController.php:123-124` | Uses `pin_hash` + `Hash::check()` - **kolom tidak ada** | Ganti ke `access_pin` comparison |
| `KioskController.php:36` | Uses `serial_number` | Ganti ke `uuid` setelah migration |
| `EdgeDeviceController.php:908-916` | Custom hash_hmac signature | Ganti ke `URL::signedRoute()` |

---

## Implementation Steps

### Phase 1: Database Migration

#### [NEW] Migration: Add uuid to rvm_machines

```php
// 2026_01_26_add_uuid_to_rvm_machines.php
Schema::table('rvm_machines', function (Blueprint $table) {
    $table->uuid('uuid')->unique()->after('id');
});

// Seed existing records with UUID
\App\Models\RvmMachine::whereNull('uuid')->each(function ($machine) {
    $machine->uuid = \Str::uuid();
    $machine->save();
});
```

---

### Phase 2: Update Route Definition

#### [MODIFY] [web.php](file:///home/my/MyRVM1/MyRVM-Server/routes/web.php)

**Before:**
```php
Route::get('/rvm-ui/{machine_uuid}', [KioskController::class, 'index'])
    ->name('kiosk.index');
```

**After:**
```php
Route::get('/rvm-ui/{uuid}', [KioskController::class, 'index'])
    ->name('kiosk.index')
    ->middleware('signed');
```

> [!IMPORTANT]
> Parameter name `{uuid}` harus sama persis dengan yang digunakan di `URL::signedRoute()`.

---

### Phase 3: Update Handshake Response

#### [MODIFY] [EdgeDeviceController.php](file:///home/my/MyRVM1/MyRVM-Server/app/Http/Controllers/Api/EdgeDeviceController.php)

Fungsi `generateSignedKioskUrl()` harus menggunakan `URL::signedRoute()`:

```php
use Illuminate\Support\Facades\URL;

private function generateSignedKioskUrl(RvmMachine $machine): string
{
    return URL::signedRoute('kiosk.index', [
        'uuid' => $machine->uuid  // UUID 36-char, bukan serial_number
    ]);
}
```

---

### Phase 4: Update KioskController

#### [MODIFY] [KioskController.php](file:///home/my/MyRVM1/MyRVM-Server/app/Http/Controllers/Dashboard/KioskController.php)

```php
public function index(Request $request, string $uuid): View|Response
{
    // Middleware 'signed' sudah validasi signature sebelum masuk sini
    
    // Lookup by UUID (36-char format)
    $machine = RvmMachine::where('uuid', $uuid)->first();
    
    if (!$machine) {
        return $this->renderErrorPage('Mesin Tidak Ditemukan', ...);
    }
    // ...
}
```

---

### Phase 5: Fix AuthController Bugs

#### [MODIFY] [AuthController.php](file:///home/my/MyRVM1/MyRVM-Server/app/Http/Controllers/Api/Kiosk/AuthController.php)

**Fix 1:** Line 104 - Lookup by uuid (sudah benar setelah migration)

**Fix 2:** Line 114-125 - PIN comparison menggunakan `access_pin`:
```php
// Old (WRONG):
->first(function ($assignment) use ($validated) {
    return $assignment->pin_hash && 
           Hash::check($validated['pin'], $assignment->pin_hash);
});

// New (CORRECT):
->first(function ($assignment) use ($validated) {
    return $assignment->access_pin === $validated['pin'] &&
           ($assignment->pin_expires_at === null || 
            $assignment->pin_expires_at > now());
});
```

---

## Verification Plan

### Step 1: Generate URL (Tinker)
```php
// docker compose exec app php artisan tinker

// Ambil UUID asli dari database
$uuid = \App\Models\RvmMachine::first()->uuid; 
// Output: "9fed2bc0-2b15-4c6e-9824-7f283287319c"

// Generate Signed URL
echo \URL::signedRoute('kiosk.index', ['uuid' => $uuid]);
```

### Step 2: Test Cases
```bash
# A. Success (200 OK)
curl -I "http://localhost/rvm-ui/9fed2bc0-...?signature=..."

# B. Invalid Signature (403 Forbidden)
curl -I "http://localhost/rvm-ui/9fed2bc0-...?signature=NGASAL"

# C. Tampered UUID (403 Forbidden)
curl -I "http://localhost/rvm-ui/9fed2bc0-...[ubah 1 karakter]?signature=..."
```

---

## Edge API Endpoints Review

| Endpoint | Status | Notes |
|----------|--------|-------|
| `POST /api/v1/edge/handshake` | ✅ Ready | X-RVM-API-KEY header |
| `POST /api/v1/edge/heartbeat` | ⚠️ Uses device ID | Perlu review |
| `POST /api/v1/edge/telemetry` | ⚠️ Uses device ID | Perlu review |
| `POST /api/v1/edge/deposit` | ❌ Not implemented | Future |

---

## Security Notes

- PIN disimpan sebagai `access_pin` (plaintext) dengan `pin_expires_at`
- Signed URL permanen (tanpa expiry) sesuai plan
- Rate limiting 5 attempts/hour untuk PIN verification
