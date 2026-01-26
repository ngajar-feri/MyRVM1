---
name: api-docs-projects-sync
description: Ensures Swagger/L5-Swagger documentation is updated whenever API endpoints are modified.
---

# Rule: API Documentation Project Sync

Guarantees that `https://myrvm.penelitian.my.id/api/documentation` reflects the actual code state and ensures synchronization across all documentation artifacts.

## 🎯 Trigger Conditions
- User Intent: **"Kita update api"**, **"Dokumentasikan api"**, or **"Sync Swagger"**.
- Code Events:
    - Adding, modifying, or deleting a route in `routes/api.php`.
    - Changing function signatures or request/response logic in Controllers.
    - Modifying `FormRequest` validation rules.

## 📋 Mandatory Update Checklist

If **ANY** API change occurs, you **MUST** rigorously update and verify these 5 critical locations:

### 1. Helper JS (Frontend Consumer)
*   **File:** `MyRVM-Server/public/js/api-helper.js`
*   **Action:** Update endpoint URLs, request methods, and expected payload structures to match the new API definition.

### 2. JSON Documentation (The Source of Truth)
*   **File:** `MyRVM-Server/storage/api-docs/api-docs.json`
*   **Action:** 
    1.  Update Controller DocBlocks (`@OA\...`).
    2.  **Mandatory Examples:** If the endpoint requires Parameters or a Request Body, you **MUST** add `example="value"` to every `@OA\Property` or `@OA\Parameter`.
    3.  Run generation command: `docker compose exec app php artisan l5-swagger:generate` (adjust `app` to your specific container name if needed).
    4.  **Verify:** Check that the generated JSON actually contains your changes (search for the endpoint/field).

### 3. Blade View (iframe/Container)
*   **File:** `MyRVM-Server/resources/views/dashboard/api-docs.blade.php`
*   **Action:** Ensure the iframe source or embedded assets point to the correct documentation version.

### 4. Swagger Config (Configuration)
*   **File:** `MyRVM-Server/config/l5-swagger.php`
*   **Action:** Verify paths, constants, and API version numbering (e.g., `v1` vs `v2`).

### 5. Vendor View (Custom UI)
*   **File:** `MyRVM-Server/resources/views/vendor/l5-swagger/index.blade.php`
*   **Action:** If custom styling or scripts are injected here, ensure they are compatible with the updated Swagger UI assets.

## 🔍 Validation & Consistency

> [!IMPORTANT]
> The documentation MUST NOT lie.

1.  **JSON vs Controller:** The structure in `api-docs.json` MUST match the actual Controller logic exactly.
2.  **Test It:** Do not assume `l5-swagger:generate` worked perfectly. Open `api-docs.json` and inspect the schema manually.
3.  **URL Check:** Confirm the public documentation URL `https://myrvm.penelitian.my.id/api/documentation` is accessible and renders without validator errors.
