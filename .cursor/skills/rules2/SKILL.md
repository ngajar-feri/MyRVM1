---
name: skill-003-ui-ux-guidance
description: Enforces mandatory user guidance (tooltips, toasts, aria-labels) for all interactive elements.
---

# Skill 003: UI/UX & User Guidance Rules

Prevents "mystery navigation" by ensuring every UI element explains itself.

## When to Use

- When writing HTML, Blade templates, React/Vue components.
- When adding buttons, icons, or complex forms.

## Instructions

### 1. Mandatory Explanations
Every interactive element must explain its function BEFORE interaction.

### 2. Tooltips for Static Elements
Use `data-bs-toggle="tooltip"` or `title` attributes for buttons/icons.
- **Bad:** `<button><i class="fa fa-trash"></i></button>`
- **Good:** `<button data-bs-toggle="tooltip" title="Delete this user permanently"><i class="fa fa-trash"></i></button>`

### 3. Accessibility
Never leave an icon alone. Always provide an `aria-label` or descriptive text.

### 4. Feedback Loops (Toasts)
After an action (save, delete, copy), trigger a Toast/Notification.
- **Logic:** `showToast('Success', 'Data saved successfully')`