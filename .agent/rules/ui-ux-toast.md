---
trigger: always_on
---

### UI/UX & USER GUIDANCE RULES
1. **Mandatory Explanations:** Every interactive element (buttons, logos, icons, complex text) MUST include a user guidance mechanism to explain its function.
2. **Tooltips for Static Elements:** Use Tooltips (`data-bs-toggle="tooltip"` or `title` attribute) for buttons, logos, and icons so users know what they do before clicking.
3. **Toasts for Actions:** Implement Toasts/Notifications for feedback after an action is performed (e.g., "Data saved", "Copied to clipboard").
4. **Descriptive Text:** Never leave an icon alone. Always provide `aria-label` or a tooltip description.

### IMPLEMENTATION EXAMPLES (Bootstrap/Tailwind)
- ❌ Bad: `<button><i class="fa fa-trash"></i></button>`
- ✅ Good (Tooltip): `<button data-bs-toggle="tooltip" data-bs-placement="top" title="Delete this user permanently"><i class="fa fa-trash"></i></button>`
- ✅ Good (Toast Logic): `showToast('Success', 'User has been deleted successfully')`