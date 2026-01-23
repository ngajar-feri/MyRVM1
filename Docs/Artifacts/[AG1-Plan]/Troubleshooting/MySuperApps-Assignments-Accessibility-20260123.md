# Artifact: Assignments Page Accessibility (Modal Focus) Error
**Date:** 2026-01-23
**Revision Sequence:** -
**Reference Change:** -
**Context:** User reported `Blocked aria-hidden on an element because its descendant retained focus` when opening the assignment details modal.

## 1. Summary
The application threw a browser intervention warning because an element inside the `#assignmentDetailModal` retained focus while the modal container still had `aria-hidden="true"`. This was caused by the code forcefully creating a `new bootstrap.Modal()` instance every time the button was clicked, without properly handling the existing instance or state transitions.

## 2. Key Decisions / Logic
- **Diagnosis**: The error confirms that the browser sees the modal as "hidden" (accessibility-wise) but focus is inside it. This invalid state typically happens when `show()` logic is flawed.
- **Root Cause**: `new bootstrap.Modal(el).show()` was called repeatedly on the same element. If an instance already existed, this could corrupt the internal state management of `aria-hidden`.
- **Fix**: Replaced `new bootstrap.Modal(...)` with `bootstrap.Modal.getOrCreateInstance(el)`. This ensures we reuse the existing Bootstrap object, allowing it to correctly toggle `aria-hidden` and manage focus.

## 3. The Output (Code Snippet)
### Before (Bug)
```javascript
// Creates a new instance every time, ignoring previous state
new bootstrap.Modal(document.getElementById('assignmentDetailModal')).show();
```

### After (Fixed)
```javascript
// Reuses existing instance if available, ensuring state consistency
const modalEl = document.getElementById('assignmentDetailModal');
const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
modal.show();
```

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-23 | - | Initial Accessibility Fix |
