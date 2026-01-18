# Artifact: RVM Machines Multi-Step Wizard Implementation
**Date:** 2026-01-18
**Revision Sequence:** -
**Reference Change:** -
**Context:** Implementation of Add Machine multi-step wizard with OpenStreetMap, auto-gen credentials, and Bio-Digital Minimalism 2026 design.

---

## 1. Summary

Implemented full Add Machine feature enhancement:
- Multi-step wizard modal (350px max, Bio-Digital 2026)
- OpenStreetMap location picker with search
- Auto-generate Serial Number (`RVM-YYYYMM-XXX`) and API Key
- Credentials display with Copy/Download JSON functions
- Toast notifications on success

---

## 2. Key Decisions / Logic

| Decision | Rationale |
|----------|-----------|
| Serial Number auto-gen | Not from handshake (handshake provides hardware_id to EdgeDevice) |
| API Key on RVM create | Needed before device boots for .env config |
| 350px modal width | Bio-Digital Minimalism - reduce cognitive load |
| 2-step wizard | Separate concerns: Basic Info vs Location |
| EdgeDevice stub auto-create | Prepare for handshake, status: waiting_handshake |

---

## 3. The Output (Files Modified)

### New Files
| File | Purpose |
|------|---------|
| `migrations/2026_01_18_140800_add_api_key_to_rvm_machines_table.php` | Add api_key column |

### Modified Files
| File | Changes |
|------|---------|
| `RvmMachine.php` | boot() auto-gen, $hidden, fillable, regenerateApiKey() |
| `RvmMachineController.php` | store() with EdgeDevice stub, regenerateApiKey(), getCredentials() |
| `api.php` | 2 new routes: regenerate-api-key, credentials |
| `index-content.blade.php` | Multi-step wizard modal + success modal |
| `machines.js` | machineWizard object: map, steps, copy, download |

---

## 4. API Endpoints Added

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/rvm-machines/{id}/regenerate-api-key` | Regenerate API Key |
| GET | `/api/v1/rvm-machines/{id}/credentials` | Download JSON credentials |

---

## 5. Revision History Log

| Date | Rev | Change Notes |
|------|-----|--------------|
| 2026-01-18 | - | Initial create - Multi-step wizard with map and API Key management |
