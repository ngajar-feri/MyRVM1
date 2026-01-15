---
trigger: always_on
---

# Advanced UI/UX & Design Systems

This skill acts as a Senior Product Designer & Frontend Architect. It enforces strict standards for visual hierarchy, interaction design, accessibility, performance, and internationalization.

## When to Use

- **Generating Frontend Code:** Whenever writing HTML, CSS (Tailwind/Bootstrap), or JS components.
- **Refactoring:** When asked to improve the "look and feel" or "usability" of an existing page.
- **Mobile Optimization:** When ensuring the app works flawlessly on phones/tablets.
- **Accessibility Audits:** When checking for screen-reader compatibility and keyboard navigation.
- **Localization Setup:** When defining how text and content are displayed to users.

## Instructions

### 1. Mobile-First & Responsive Architecture (Priority #1)
- **Start Small:** Design/Code for mobile screens first, then use `min-width` media queries for larger screens.
- **Fluid Layouts:** REJECT fixed pixels (`px`) for containers. Use relative units (`%`, `fr`, `rem`, `em`) for widths and padding.
- **Touch Targets:** ALL interactive elements must be at least **44x44 pixels**. Add adequate padding.
- **Thumb Zones:** Place critical actions (Save, Buy, Navigate) in the lower third of the screen for mobile ease-of-use.
- **Off-Canvas Patterns:** Move complex secondary content (filters, detailed menus) to off-canvas drawers on small screens.

### 2. Visual Hierarchy & Interaction Design
- **Scanability:** Use layout and typography to guide the eye. Largest/Boldest = Most Important.
- **Whitespace:** Use generous, consistent spacing to reduce cognitive load. Don't clutter.
- **Feedback Loops:**
    - **Hover:** For desktop cues.
    - **Focus:** For keyboard navigation (never remove `outline` without replacement).
    - **Active/Loading:** Buttons must show a loading spinner or disabled state immediately after clicking to prevent double-submit.
- **Animation:** Use "judiciously". Micro-interactions (like a heart pop) are good; massive layout shifts are bad.

### 3. Strict Accessibility (WCAG 2.1 AA)
- **Contrast:** Text vs Background ratio must be at least 4.5:1.
- **Semantics:** Use `<button>` for actions, `<a>` for navigation. Do not use `<div>` with `onClick` unless absolutely necessary (and properly ARIA-tagged).
- **No Lonely Icons:** Every icon (`<i class="...">`) MUST have an `aria-label` or a hidden text span describing it.
- **Alt Text:** All informational images require `alt` text. Decorative images use `alt=""`.
- **Keyboard Trap:** Ensure users can Tab INTO and OUT OF all modals and menus.

### 4. Performance & Core Web Vitals
- **LCP (Largest Contentful Paint):** Optimize the main hero image/text. Preload critical assets.
- **CLS (Cumulative Layout Shift):** Always define `width` and `height` attributes for images/videos to prevent layout jumping while loading.
- **Lazy Loading:** Add `loading="lazy"` to all non-critical images and iframes.
- **Code Splitting:** Don't load massive JS libraries for a simple page.

### 5. Forms & Data Entry
- **Input Types:** Use specific HTML5 types (`email`, `tel`, `number`, `date`) to trigger the correct mobile keyboard.
- **Inline Validation:** Show errors *next to* the field immediately after the user leaves the field (onBlur), not just after clicking "Submit".
- **Labels:** Never rely solely on `placeholder`. Always have a visible `<label>`.

### 6. Internationalization (i18n) & Localization
- **Rule #1: NO HARDCODING.** Never write raw text directly in the view (e.g., `<h1>Welcome</h1>` is forbidden).
- **Default Languages:** Support **English (en)** as primary and **Indonesian (id)** as secondary.
- **Implementation Strategy:**
    - **Static UI (Labels, Buttons, Menus):** Use the framework's file-based localization (e.g., Laravel 12 JSON files `lang/en.json`, `lang/id.json` or helper `__('messages.welcome')`).
    - **Dynamic Content (Database):** For user-generated content or product data, use a database strategy (e.g., `translatable` tables or JSON columns) to store multi-language data.
- **Design Adaptability:** Design UI components to handle text expansion. Indonesian text is often 20-30% longer than English. Ensure buttons and containers can grow/wrap gracefully without breaking the layout.

### 7. Testing & Quality Assurance
- **The "Fat Finger" Test:** Can a user tap a button without accidentally hitting the one next to it?
- **The "Sunlight" Test:** Is the contrast high enough to read on a phone screen outdoors?
- **The "Translation" Test:** Switch the language to Indonesian. Does the UI break? Does text overflow buttons?
- **Error Recovery:** Error messages must explain *what* went wrong and *how* to fix it (e.g., instead of "Error 500", say "We couldn't save your profile. Please check your internet and try again").