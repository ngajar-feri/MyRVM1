---
description: API Documentation Sync
---

# API Documentation Sync

Guarantees that `http://localhost:8000/api/documentation` reflects the actual code state.

## When to Use

- When adding, modifying, or deleting an API route.
- When changing Request/Response parameters in Controllers.

## Instructions

### 1. Update Checklist
If an API change occurs, you **MUST** update and verify the following 5 locations:

1. **Helper JS:** `/public/js/api-helper.js`
2. **JSON Docs:** `/storage/api-docs/api-docs.json`
3. **Blade View:** `/resources/views/dashboard/api-docs.blade.php`
4. **Swagger Config:** `/config/l5-swagger.php`
5. **Vendor View:** `/resources/views/vendor/l5-swagger/index.blade.php`

### 2. Consistency
Ensure the JSON structure in `api-docs.json` matches the actual Controller logic exactly.