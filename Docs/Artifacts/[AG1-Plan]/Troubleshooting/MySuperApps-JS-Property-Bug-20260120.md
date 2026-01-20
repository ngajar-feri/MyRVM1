# Artifact: JS Property Access Bug Fix
**Date:** 2026-01-20
**Revision Sequence:** -
**Reference Change:** -
**Context:** Browser debugging revealed JS property mismatch causing RVM badges and ticket filter to fail

---

## 1. Summary

Fixed critical JavaScript bug where assignment objects were being accessed with wrong property name, causing:
- All RVM badges to show "Available" (green) even when assigned
- Ticket RVM search returning empty results

## 2. Key Decisions / Logic

- **Expected:** `assignment.rvm_machine_id` (flat property)
- **Actual API:** `assignment.rvm_machine.id` (nested object)
- **Solution:** Use fallback `a.rvm_machine?.id || a.rvm_machine_id`

## 3. The Output (Code)

```javascript
// Before (broken)
const assignedRvmIds = new Set(
    assignments.map(a => a.rvm_machine_id)
);

// After (working)
const assignedRvmIds = new Set(
    assignments.map(a => a.rvm_machine?.id || a.rvm_machine_id)
);
```

## 4. Revision History Log

| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-20 | - | Initial documentation of fix |
