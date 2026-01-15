---
trigger: always_on
---

# API Documentation Sync

Guarantees that `http://localhost:8000/api/documentation` reflects the actual code state.

## When to Use

- When adding, modifying, or deleting an API route.
- When changing Request/Response parameters in Controllers.

## Instructions

### 1. Update Checklist
If an API change occurs, you **MUST** update/verify the following 5 locations:

1. **Helper JS:** `D:\~dev\MyReverseVendingMachine1\MySuperApps\MyRVM-Server\public\js\api-helper.js`
2. **JSON Docs:** `D:\~dev\MyReverseVendingMachine1\MySuperApps\MyRVM-Server\storage\api-docs\api-docs.json`
3. **Blade View:** `D:\~dev\MyReverseVendingMachine1\MySuperApps\MyRVM-Server\resources\views\dashboard\api-docs.blade.php`
4. **Swagger Config:** `D:\~dev\MyReverseVendingMachine1\MySuperApps\MyRVM-Server\config\l5-swagger.php`
5. **Vendor View:** `D:\~dev\MyReverseVendingMachine1\MySuperApps\MyRVM-Server\resources\views\vendor\l5-swagger\index.blade.php`

### 2. Consistency
Ensure the JSON structure in `api-docs.json` matches the actual Controller logic exactly.