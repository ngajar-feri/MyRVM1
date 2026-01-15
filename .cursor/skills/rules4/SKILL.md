---
name: skill-002-dev-plan-storage
description: Standardizes how development plans and documentation are saved and versioned.
---

# Skill 002: Development Plan Storage

Ensures all development plans are documented, versioned, and saved to the specific directory structure.

## When to Use

- When creating a development plan, roadmap, or technical specification.
- Before starting a complex coding task (planning phase).

## Instructions

### 1. Target Directory
Save all files to: `D:\~dev\MyReverseVendingMachine1\MySuperApps\Docs\PLAN\[CRS1-Plan]`

### 2. Filename Convention
Format: `[CRS1]-[kebab-case-name]-[timestamp].md`
- **Timestamp format:** `DayName-DDMMYYYY-HHMM`
- **Example:** `[CRS1]-user-role-mapping-Senin-01012024-1400.md`

### 3. Document Header (Mandatory)
Every file must start with this header structure:

```text
Versi Dokumen: [X.Y] (Increment from previous)
Tanggal Revisi: [Hari-DD Bulan Tahun - HH:MM AM/PM] (e.g., Kamis-08 Januari 2026 - 02:45 PM)
Tujuan: [Description of goals and scope]
Status: [Selesai | Belum]