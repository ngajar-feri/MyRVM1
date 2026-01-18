# Implementation Plan: MyRVM Dashboard Restructure v2

**Date:** 2026-01-18
**Status:** Approved for Execution

---

## Goal

Major restructure per [pemisahan-konsep-rvm-edge-assignment-ticket.md](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/Docs/PLAN/%5BAIS1-Plan%5D/pemisahan-konsep-rvm-edge-assignment-ticket.md):
1. Remove Edge Devices menu (data shown in RVM Details)
2. Create `technician_assignments` table (static access rights)
3. Create `maintenance_tickets` table (work orders)
4. Update sidebar with new sub-menus

---

## Execution Phases

### Phase 1: Database Migrations
| Migration | Status |
|-----------|--------|
| `technician_assignments` | Pending |
| `maintenance_tickets` | Pending |

### Phase 2: Sidebar Update
| Task | Status |
|------|--------|
| Remove Edge Devices menu | Pending |
| Keep: User & Tenants (Master, Assignments) | Done |
| Keep: RVM Machines (Master, Tickets) | Done |

### Phase 3: RVM Details Page
- Show Edge Device info (auto from Handshake)
- Health Metrics cards
- Linked technicians list

### Phase 4: Assignment & Ticket UI
- Assignments management page
- Maintenance Tickets management page

---

## Files to Modify

| File | Change |
|------|--------|
| [app.blade.php](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/resources/views/layouts/app.blade.php) | Remove Edge Devices menu item |
| New migration | `technician_assignments` |
| New migration | `maintenance_tickets` |
| New model | `TechnicianAssignment.php` |
| New model | `MaintenanceTicket.php` |

---

## Reference

📄 PRD: [Docs/Project/Requirements/MySuperApps-PRD-v2.0.md](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/Docs/Project/Requirements/MySuperApps-PRD-v2.0.md)
📋 Specs: [Docs/PLAN/[AIS1-Plan]/pemisahan-konsep-rvm-edge-assignment-ticket.md](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/Docs/PLAN/%5BAIS1-Plan%5D/pemisahan-konsep-rvm-edge-assignment-ticket.md)
