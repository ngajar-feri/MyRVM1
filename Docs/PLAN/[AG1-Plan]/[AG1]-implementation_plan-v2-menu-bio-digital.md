# Implementation Plan: RVM Machines UI Enhancement Phase 2

**Date:** 2026-01-18
**Revision:** 2
**Status:** Awaiting Approval

---

## Goal

Enhance the RVM Machines module and sidebar per Bio-Digital Minimalism 2026:

| # | Task | Notes |
|---|------|-------|
| 1 | Add Machine Modal with OpenStreetMap | Like Assignment modal |
| 2 | Address field **editable** (not readonly) | User feedback |
| 3 | Auto-generate Serial Number + API Key | On machine creation |
| 4 | API Key features: Regenerate, Copy, Download JSON | User feedback |
| 5 | Sidebar cleanup per Bio-Digital Minimalism | Reduce cognitive load |
| 6 | Mark unused files with `.bak` extension | User feedback |

---

## User Feedback Summary

> **1. Unused Files:** Add `.bak` extension to files not in use.

> **2. API Key Features:** Add regenerate, copy, and download [.json](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/package.json) with Serial Number + API Key.

> **3. Address Editable:** Address field should be editable (not readonly).

> **4. Sidebar Review:** Focus on 3 core menus: User & Tenants, RVM Machines, Edge Devices. Apply Bio-Digital Minimalism principles.

---

## Sidebar Structure (Bio-Digital Minimalism 2026)

### Current State (Too Many Items = Cognitive Overload)
```
Dashboard
├─ Management
│  ├─ User & Tenants ✅ Keep
│  ├─ RVM Machines ✅ Keep
│  ├─ Edge Devices ✅ Keep
│  └─ CV Servers ❌ Move/Hide
├─ Monitoring
│  ├─ System Health ❌ Merge into Dashboard
│  ├─ Transactions ❌ Move to RVM Machines sub-menu
│  ├─ Logs ⚠️ Keep (technical)
│  └─ API Documentation ⚠️ Dev only
```

### Proposed State (Bio-Digital: Calm, Focused)
```
Dashboard (Home)

─ Core Modules ─
├─ User & Tenants
├─ RVM Machines
│  └─ (Transactions inside RVM details)
└─ Edge Devices

─ Developer ─ (Hidden by default, toggle in settings)
├─ Logs
└─ API Documentation
```

**Principles Applied:**
- **Cognitive Sustainability:** Max 5 visible menu items
- **Intent-Based Display:** Developer menu hidden by default
- **Calm Technology:** Reduce visual noise, group logically

---

## Proposed Changes

### 1. Sidebar Simplification

#### [MODIFY] [app.blade.php](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/resources/views/layouts/app.blade.php)

- Remove CV Servers menu (or add `.bak` to view file)
- Merge System Health into Dashboard widgets
- Hide Developer menu (Logs, API Docs) behind toggle/role check
- Rename "Management" header to "Core Modules"

---

### 2. Add Machine Modal with OpenStreetMap

#### [MODIFY] [index-content.blade.php](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/resources/views/dashboard/machines/index-content.blade.php)

New Modal Structure:
```
┌─────────────────────────────────────┐
│ Add New RVM Machine                 │
├─────────────────────────────────────┤
│ Name: [________________]            │
│                                     │
│ Location:                           │
│ [Search...        ] [🔍]            │
│ ┌─────────────────────────────┐     │
│ │        OpenStreetMap        │     │
│ │        (Click to pin)       │     │
│ └─────────────────────────────┘     │
│                                     │
│ Address: [__________________] ✏️    │  ← EDITABLE
│ LAT: [-6.1234] LNG: [106.8765]      │
│                                     │
│ Status: [Offline ▼]                 │
│ Notes: [________________]           │
├─────────────────────────────────────┤
│              [Cancel] [Save]        │
└─────────────────────────────────────┘
```

---

### 3. API Key Management Features

#### New UI in Machine Details/Edit View

```
┌─ API Credentials ─────────────────────┐
│                                       │
│ Serial Number: RVM-202601-001         │
│                                       │
│ API Key: ••••••••••••••••••••        │
│ [👁 Show] [📋 Copy] [🔄 Regenerate]   │
│                                       │
│ [📥 Download JSON]                    │
│                                       │
└───────────────────────────────────────┘
```

**Download JSON Format:**
```json
{
  "serial_number": "RVM-202601-001",
  "api_key": "sk_live_abc123...",
  "name": "RVM Mall Grand Indonesia",
  "generated_at": "2026-01-18T12:00:00Z"
}
```

---

### 4. Files to Mark as Unused (.bak)

After review, the following files will be renamed:

| Current | New | Reason |
|---------|-----|--------|
| `cv-servers/index-content.blade.php` | `.bak` | Menu hidden |
| (TBD after implementation) | | |

---

### 5. Database Migration

#### [NEW] Migration: Add columns to `rvm_machines`

```php
$table->string('api_key', 64)->unique()->nullable();
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->text('address')->nullable();
```

---

### 6. Model Updates

#### [MODIFY] [RvmMachine.php](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/app/Models/RvmMachine.php)

- Add `api_key`, `latitude`, `longitude`, `address` to `$fillable`
- Add `$hidden = ['api_key']`
- Add boot method for auto-generation:
  ```php
  protected static function boot() {
      parent::boot();
      static::creating(function ($model) {
          $model->serial_number = 'RVM-' . date('Ym') . '-' . str_pad(RvmMachine::count() + 1, 3, '0', STR_PAD_LEFT);
          $model->api_key = Str::random(64);
      });
  }
  ```

---

### 7. API Controller Updates

#### [MODIFY] [RvmMachineController.php](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/app/Http/Controllers/Api/RvmMachineController.php)

**New Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/rvm-machines/{id}/regenerate-api-key` | Generate new API key |
| `GET` | `/api/v1/rvm-machines/{id}/credentials` | Get JSON with SN + API Key |

---

## Verification Plan

### Manual Testing
1. Open RVM Machines → Add Machine
2. Verify map loads and search works
3. Edit address field manually → verify saves
4. View Machine Details → verify API Key section
5. Test Copy, Regenerate, Download JSON
6. Verify sidebar shows only 3 core menus

### Rollback Plan
```bash
# Revert sidebar changes
git checkout -- resources/views/layouts/app.blade.php

# Revert migration
php artisan migrate:rollback --step=1
```
