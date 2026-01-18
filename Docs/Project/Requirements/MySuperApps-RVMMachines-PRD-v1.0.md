# PRD: RVM Machines UI Enhancement Phase 2
**Version:** 1.0
**Status:** Draft
**Last Updated:** 2026-01-18

---

## 1. Executive Summary

Meningkatkan modul **Add RVM Machine** dengan integrasi peta OpenStreetMap dan auto-generasi kredensial (Serial Number + API Key). Mengikuti prinsip **Bio-Digital Minimalism 2026** untuk UI yang menenangkan dan mengurangi beban kognitif operator.

**Key Features:**
- Multi-step wizard modal (max 350px width)
- OpenStreetMap location picker
- Auto-generate Serial Number & API Key
- API Key management (Copy, Regenerate, Download JSON)

---

## 2. User Personas

| Persona | Role | Needs |
|---------|------|-------|
| **Super Admin** | Sistem administrator | Menambah RVM, manage credentials |
| **Admin** | Operator lapangan | Monitoring mesin, view API key |
| **Teknisi** | Field technician | Menerima API Key untuk setup Jetson |

---

## 3. Functional Requirements (MoSCoW Method)

### Must Have (P0) - MVP
- [ ] Multi-step Add Machine wizard (2 steps)
- [ ] Step 1: Name, Status, Notes
- [ ] Step 2: OpenStreetMap location picker
- [ ] Auto-generate Serial Number format `RVM-YYYYMM-XXX`
- [ ] Auto-generate API Key (64 char random)
- [ ] Editable address field
- [ ] Show API Key di success dialog

### Should Have (P1)
- [ ] Copy API Key button
- [ ] Regenerate API Key button
- [ ] Download JSON credentials
- [ ] Auto-create EdgeDevice stub on RVM creation

### Could Have (P2)
- [ ] QR Code untuk API Key
- [ ] Email credentials ke teknisi
- [ ] Bulk import RVM dari CSV

### Won't Have (Out of Scope)
- CV Servers integration (hidden)
- Mobile app changes
- Real-time map tracking

---

## 4. Technical Constraints

| Component | Technology |
|-----------|------------|
| **Stack** | Laravel 12+ (PHP 8.3) |
| **Database** | PostgreSQL |
| **Frontend** | Blade + Bootstrap 5 + Vanilla JS |
| **Map** | Leaflet.js 1.9.4 + OpenStreetMap + Nominatim |
| **Environment** | Docker Compose |

---

## 5. Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Add Machine wizard completion | < 30 seconds | User testing |
| API Key copy success | 100% | Error logs |
| Map load time | < 2 seconds | Browser DevTools |
| User satisfaction | Positive feedback | Manual review |

---

## 6. Implementation Tasks (To-Do)

### Phase 1: Database & Model
- [ ] Create migration: add `api_key`, `latitude`, `longitude`, `address` to `rvm_machines`
- [ ] Update `RvmMachine.php` model with auto-generation boot method
- [ ] Add API Key to hidden attributes

### Phase 2: API Controller
- [ ] Update `store()` method with auto-gen logic
- [ ] Add `POST /regenerate-api-key` endpoint
- [ ] Add `GET /credentials` endpoint (download JSON)
- [ ] Update OpenAPI/Swagger documentation

### Phase 3: Frontend UI
- [ ] Create multi-step wizard modal (350px max width)
- [ ] Step 1: Name, Status, Notes fields
- [ ] Step 2: Map search + picker + editable address
- [ ] Success modal with API Key display
- [ ] Add Copy/Regenerate/Download buttons

### Phase 4: Testing & Documentation
- [ ] Browser test all features
- [ ] Update API Documentation (5 locations per workflow)
- [ ] Create artifact with changes

---

## 7. UI Design Specification

### Modal Wizard (Bio-Digital Minimalism 2026)

```
┌─────────────────────────────────────┐
│        Add New RVM Machine          │
│  ● ○ (Step indicators)             │
├─────────────────────────────────────┤
│ Step 1: Basic Info                  │
│                                     │
│ Name: [______________]              │
│ Status: [Offline ▼]                 │
│ Notes: [______________]             │
│                                     │
│            [Next →]                 │
└─────────────────────────────────────┘
Max width: 350px
```

```
┌─────────────────────────────────────┐
│        Add New RVM Machine          │
│  ○ ● (Step indicators)             │
├─────────────────────────────────────┤
│ Step 2: Location                    │
│                                     │
│ [Search...          ] [🔍]          │
│ ┌─────────────────────────────┐     │
│ │      OpenStreetMap          │     │
│ └─────────────────────────────┘     │
│ Address: [____________] ✏️          │
│                                     │
│      [← Back]    [Save ✓]           │
└─────────────────────────────────────┘
```

### Success Modal
```
┌─────────────────────────────────────┐
│    ✓ RVM Berhasil Ditambahkan       │
├─────────────────────────────────────┤
│ Serial: RVM-202601-001              │
│ API Key: ••••••••••••               │
│                                     │
│ [👁 Show] [📋 Copy] [📥 JSON]        │
│                                     │
│            [Selesai]                │
└─────────────────────────────────────┘
```

---

## 8. Document History & References

| Version | Date | Changes | Reference |
|---------|------|---------|-----------|
| 1.0 | 2026-01-18 | Initial Draft | User discussion in chat |
