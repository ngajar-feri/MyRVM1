# Artifact: RVM Machines UI Enhancement Phase 2 - Implementation Plan
**Date:** 2026-01-18
**Revision Sequence:** -
**Reference Change:** -
**Context:** User request to enhance Add Machine modal with OpenStreetMap, auto-generation, and Bio-Digital Minimalism 2026 design.

---

## 1. Summary

Implementasi enhancement untuk modul RVM Machines:
- Multi-step wizard modal (max 350px, Bio-Digital Minimalism)
- OpenStreetMap integration untuk location picker
- Auto-generate Serial Number + API Key
- API Key management (Copy, Regenerate, Download JSON)
- Sidebar cleanup (CV Servers hidden)

---

## 2. Key Decisions / Logic

### A. Serial Number & API Key
- **Serial Number:** Auto-generated format `RVM-YYYYMM-XXX` saat create
- **API Key:** Auto-generated 64 char random, tersimpan di `rvm_machines`
- **Bukan dari Handshake:** Handshake hanya memberikan `hardware_id` ke edge_devices

### B. Sidebar Structure
- Keep "Management" header (not "Core Modules")
- Logs & API Docs sudah role-based, keep as is
- CV Servers: rename view file to `.bak`

### C. Modal Design Bio-Digital Minimalism
- Max width: **350px**
- Multi-step wizard (2 steps)
- Typography: Bold sans-serif, high contrast
- Clean white space
- Smooth fade transitions

### D. Address Field
- **Editable** (not readonly)
- Auto-filled dari reverse geocoding, tapi bisa diubah manual

---

## 3. The Output (Files to Modify)

### Database Migration
```php
// database/migrations/2026_01_18_add_api_credentials_to_rvm_machines.php
Schema::table('rvm_machines', function (Blueprint $table) {
    $table->string('api_key', 64)->unique()->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->text('address')->nullable();
});
```

### Model Update
```php
// app/Models/RvmMachine.php
protected $fillable = [
    'name', 'location', 'serial_number', 'status', 
    'api_key', 'latitude', 'longitude', 'address',
    'capacity_percentage', 'last_ping'
];

protected $hidden = ['api_key'];

protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
        $count = RvmMachine::count() + 1;
        $model->serial_number = 'RVM-' . date('Ym') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $model->api_key = Str::random(64);
    });
}
```

### New API Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/rvm-machines/{id}/regenerate-api-key` | Regenerate API Key |
| GET | `/api/v1/rvm-machines/{id}/credentials` | Download JSON credentials |

### API Documentation Sync (5 Locations)
When API changes, update:
1. `/public/js/api-helper.js`
2. `/storage/api-docs/api-docs.json`
3. `/resources/views/dashboard/api-docs.blade.php`
4. `/config/l5-swagger.php`
5. `/resources/views/vendor/l5-swagger/index.blade.php`

---

## 4. Revision History Log

| Date | Rev | Change Notes |
|------|-----|--------------|
| 2026-01-18 | - | Initial create - Implementation plan for RVM Machines Phase 2 |
