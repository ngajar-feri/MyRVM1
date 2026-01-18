# PRD: MyRVM Dashboard Restructure v2.0
**Version:** 2.0
**Status:** Draft
**Last Updated:** 2026-01-18

---

## 1. Executive Summary

Restructure the MyRVM Dashboard following the **Bio-Digital Minimalism 2026** design philosophy and new architectural decisions that simplify data flow by removing manual Edge Device registration. The key insight: Edge Devices are automatically discovered via Handshake, eliminating the need for a separate menu.

### Core Changes
| Change | Rationale |
|--------|-----------|
| Remove Edge Devices menu | Data auto-filled via Handshake, shown in RVM Details |
| Create `technician_assignments` table | Static access rights (who CAN access) |
| Create `maintenance_tickets` table | Transactional work orders (who IS doing what) |
| Simplify sidebar to 2 main menus | Reduce cognitive load |

---

## 2. User Personas

- **Admin (Super Admin):** Full access. Creates RVM Machines, assigns technicians, creates tickets.
- **Operator:** Views machines, creates tickets, monitors status.
- **Teknisi:** Receives assignments/tickets, generates PIN, performs maintenance.

---

## 3. Functional Requirements (MoSCoW Method)

### Must Have (P0)
- [x] RVM Machine Add (Name, Location, OpenMap) → Auto-gen Serial + API Key
- [ ] Remove Edge Devices menu from sidebar
- [ ] RVM Details page shows linked Edge Device info (auto via Handshake)
- [ ] `technician_assignments` migration + model
- [ ] `maintenance_tickets` migration + model
- [ ] Assignments sub-menu under User & Tenants
- [ ] Maintenance Tickets sub-menu under RVM Machines

### Should Have (P1)
- [ ] Ticket creation wizard (select RVM → select Technician → describe issue)
- [ ] Technician App: view assigned machines + tickets
- [ ] Proof image upload for ticket resolution

### Could Have (P2)
- [ ] AI-generated status summaries (Bio-Digital "Insight Cards")
- [ ] Circadian-adaptive color theme (morning blue → evening amber)

### Won't Have (This Phase)
- CV Servers management
- Edge Device manual registration form

---

## 4. Technical Constraints

| Constraint | Value |
|------------|-------|
| **Stack** | Laravel 12+ (PHP 8.3), PostgreSQL, Redis |
| **Frontend** | Blade + Bootstrap + Leaflet (maps) |
| **Design** | Bio-Digital Minimalism 2026, 350px modals |
| **Auth** | Sanctum (Bearer Token) |

---

## 5. Data Flow (Top-Down)

```
1. Admin creates RVM Machine (Name + Location)
   ├── System generates UUID + API Key
   └── Status: inactive
   
2. Technician installs hardware
   ├── Input API Key → Nyalakan
   └── Handshake → edge_devices auto-filled
   
3. Admin assigns Technician to RVM (static access)
   └── technician_assignments record created
   
4. Admin creates Maintenance Ticket (when needed)
   ├── Assign to Technician (must have access)
   └── Technician works → completes → uploads proof
```

---

## 6. Sidebar Structure (Final)

| Menu | Sub-Items | Route |
|------|-----------|-------|
| **Dashboard** | - | `/dashboard` |
| **User & Tenants** | Master Data | `/dashboard/users` |
| | Assignments | `/dashboard/assignments` |
| **RVM Machines** | Master Data | `/dashboard/machines` |
| | Maintenance Tickets | `/dashboard/tickets` |

> **Note:** Edge Devices menu REMOVED. Device info shown in RVM Details.

---

## 7. Implementation Tasks (To-Do)

### Phase 1: Database Schema ✅
- [x] Migration: `api_key` to `rvm_machines`
- [ ] Migration: `technician_assignments` table
- [ ] Migration: `maintenance_tickets` table

### Phase 2: Models & Relationships
- [ ] `TechnicianAssignment` model + relationships
- [ ] `MaintenanceTicket` model + relationships
- [ ] Update `RvmMachine` model (hasMany tickets, hasManyThrough technicians)
- [ ] Update `User` model (hasMany assignments, hasMany tickets as assignee)

### Phase 3: Sidebar UI (Bio-Digital 2026)
- [x] Restructure to expandable menus
- [ ] **Remove Edge Devices menu entirely**
- [ ] Add Assignments sub-menu
- [ ] Add Maintenance Tickets sub-menu

### Phase 4: RVM Details Page
- [ ] Show linked Edge Device info (Device ID, IP, Camera, AI Model)
- [ ] Health Metrics cards
- [ ] Technician assignment list

### Phase 5: Assignment Management
- [ ] Assignments list page
- [ ] Add assignment modal (select User + RVM)
- [ ] Remove assignment action

### Phase 6: Maintenance Tickets
- [ ] Tickets list page (filter by status/priority)
- [ ] Create ticket modal
- [ ] Ticket detail view with status updates
- [ ] Proof image upload

---

## 8. Bio-Digital 2026 Design Principles

| Principle | Implementation |
|-----------|----------------|
| **Cognitive Sustainability** | Biophilic colors, AI summaries, smooth transitions |
| **Organic Forms** | Super-ellipse buttons, 350px modals, soft shadows |
| **Reduced Visual Noise** | Clean cards, minimal text, intent-based info display |

---

## 9. Document History & References

| Version | Date | Changes | Reference |
|---------|------|---------|-----------|
| 1.0 | 2026-01-18 | Initial Add Machine wizard | - |
| 2.0 | 2026-01-18 | Major restructure: Remove Edge menu, add Assignments/Tickets | `pemisahan-konsep-rvm-edge-assignment-ticket.md` |
