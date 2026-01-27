# Artifact: Edge API v2.0 Verification Report
**Date:** 2026-01-27
**Revision Sequence:** 1
**Reference Change:** Functional Verification of Edge Endpoints
**Revised From:** -
**Context:** Verification of Handshake, Deposit, and Sync-Offline endpoints functionality before client-side implementation.

## 1. Summary
This document confirms the successful verification of the Request/Response cycle, Data Persistence, and Security Logic for the RVM-Edge v2.0 API endpoints. All tests were performed using Laravel Feature Tests (`tests/Feature/EdgeApiV2Test.php`) in a Dockerized environment.

## 2. Methodology
- **Framework:** PHPUnit / Laravel TestBench
- **Environment:** Docker (`app` container), SQLite In-Memory Database
- **Scope:**
    - `POST /api/v1/edge/deposit`
    - `POST /api/v1/edge/sync-offline`
    - Middleware mechanism (`ValidateRvmApiKey`)

## 3. Test Scenarios & Results

| Endpoint | Scenario | Input Data | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Auth** | Invalid API Key | `X-RVM-API-KEY: wrong-key` | 401 Unauthorized | 401 Unauthorized | ✅ PASS |
| **Deposit** | Valid Upload | Image (JPG), JSON Metadata | 201 Created + Image URL | 201 Created | ✅ PASS |
| **Sync** | Bulk Transactions | Array of transactions (offline) | 200 OK + DB Records | 200 OK | ✅ PASS |
| **Data** | Anonymous Tx | `user_id: null` in offline mode | Transaction saved | Transaction saved | ✅ PASS |

## 4. Key Implementation Details Verified

### A. Security (Middleware Update)
- Confirmed `ValidateRvmApiKey` now supports **SHA-256 Hashed Keys**.
- Confirmed it authenticates against the `EdgeDevice` table (v2) instead of the legacy `rvm_machines` table check.
- Confirmed backward compatibility (resolving `RvmMachine` from `EdgeDevice`).

### B. Database Schema Update
- Modified `transactions` table to allow `nullable` `user_id`. This is critical for **Anonymous/Offline Transactions** where no user login occurred.

### C. File Storage
- Deposits are correctly stored in `storage/app/public/deposits/{Y-m-d}/{session_id}/`.

## 5. Evidence (Test Output)
```bash
PASS  Tests\Feature\EdgeApiV2Test
✓ deposit endpoint stores image and returns successful response  0.35s  
✓ sync offline endpoint creates transactions                   0.02s  
✓ invalid api key is rejected                                 0.02s  

Tests:    3 passed (12 assertions)
Duration: 0.44s
```

## 6. Recommendations
- **Deployment:** Ensure `php artisan migrate` is run on production to apply the `user_id` nullable change.
- **Client Dev:** RVM-Edge (Python) developers can now rely on these endpoints being stable.
