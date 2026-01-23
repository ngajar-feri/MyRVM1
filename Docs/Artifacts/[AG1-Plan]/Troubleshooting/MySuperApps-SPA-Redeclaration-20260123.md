# Artifact: Assignments Page Troubleshooting Log
**Date:** 2026-01-23
**Revision Sequence:** 5
**Reference Change:** Forced removal of `aria-hidden` via JS (Final Accessibility Fix).
**Context:** User reported that `Blocked aria-hidden` errors persisted despite adding correct HTML attributes.

## 1. Summary
The `Blocked aria-hidden` error indicates that the modal container still has `aria-hidden="true"` when it receives focus. Since Bootstrap's internal state management sometimes fails to remove this attribute (especially with complex nesting or race conditions), we implemented a "Brute Force" Javascript fix.

## 2. Issues & Fixes

### Issue 1: SPA Navigation Variable Redeclaration (Rev 1)
- **Fix**: Converted `const` global variables to `window.` properties.

### Issue 2: Undefined RVM ID (Rev 2)
- **Fix**: Updated frontend to use `a.rvm_machine?.id`.

### Issue 3: Modal Backdrop / White Layer (Rev 3)
- **Fix**: Corrected DOM nesting logic and removed extra closing divs.

### Issue 4: Accessibility Focus Error (Rev 4)
- **Fix**: Added `role="dialog"` and `aria-modal="true"`.

### Issue 5: Persistent Aria-Hidden Block (Rev 5)
- **Problem**: `aria-hidden="true"` persisted on open modals, blocking focus.
- **Root Cause**: Bootstrap JS failing to toggle the attribute fast enough or at all.
- **Fix**: Modified `index-content.blade.php` to explicitly call `element.removeAttribute('aria-hidden')` immediately after `modal.show()`.
- **Locations**: `viewAssignment`, `generatePin`, `regenerateApiKey`, `addAssignmentForm`.

## 3. Code Fix
```javascript
// Before
modal.show();

// After
modal.show();
modalEl.removeAttribute('aria-hidden'); // Force removal
```

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-23 | - | Initial Fix for SPA Navigation (User/Role vars) |
| 2026-01-23 | 1 | Fixed `assignmentSearch` redeclaration |
| 2026-01-23 | 2 | Fixed `undefined` RVM ID in Regenerate API Key button |
| 2026-01-23 | 3 | Fixed Modal DOM nesting and backdrop issue |
| 2026-01-23 | 4 | Fixed `aria-hidden` Accessibility errors (HTML attributes) |
| 2026-01-23 | 5 | Forced removal of `aria-hidden` via JS |
