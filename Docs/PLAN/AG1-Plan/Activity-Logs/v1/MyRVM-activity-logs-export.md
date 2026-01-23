# Artifact: Activity Logs Export Feature (PDF/Excel)
**Date:** 2026-01-23
**Revision Sequence:** -
**Reference Change:** -
**Context:** User requested "Export to PDF and Excel" for Activity Logs, adhering to Bio-Digital Minimalism and Project Standards.

## 1. Summary
The current system only supports a basic CSV export via API linkage (which returns JSON currently).
We will implement true file generation for **Excel (.xlsx)** and **PDF (.pdf)** formats.
This requires installing standard Laravel packages and updating the UI to a "Bio-Digital" style dropdown menu.

## 2. Key Decisions / Logic
- **Dependencies**:
    - `maatwebsite/excel`: The de-facto standard for Laravel Excel exports.
    - `barryvdh/laravel-dompdf`: Reliable HTML-to-PDF converter.
    - *Decision*: Install these packages to ensure "neat" (rapi) output as requested.
- **UI/UX (Bio-Digital)**:
    - Replace the single "Export" button with a **Split Button** or **Dropdown**.
    - **Visuals**: Use "Glassmorphism" dropdown menu, clean typography, and icons (`tabler-file-spreadsheet`, `tabler-file-type-pdf`).
- **Backend Architecture**:
    - **Excel**: Use an `Export` class (`App\Exports\ActivityLogExport`) to map data to columns cleanly.
    - **PDF**: Load a dedicated blade view (`dashboard.logs.pdf`) for styling.
    - **Controller**: Add `export(Request $request)` method to `LogController` handling `format=pdf|excel`.

## 3. Implementation Steps

### Phase 1: Dependencies
Run the following inside Docker container:
```bash
composer require maatwebsite/excel barryvdh/laravel-dompdf
```

### Phase 2: Backend
1.  **Create Export Class**: `php artisan make:export ActivityLogExport --model=ActivityLog`
    - Map columns: Time, User, Module, Action, Description, IP.
2.  **Controller Update**:
    - Add `export` method.
    - If `format` is 'excel', return `Excel::download`.
    - If `format` is 'pdf', load view and `stream/download`.

### Phase 3: Frontend (UI/UX)
1.  **Update `index-content.blade.php`**:
    - Change "Export" button to a Bootstrap Dropdown.
    - Apply Bio-Digital styles (soft shadows, rounded corners).
2.  **Update `logs.js`**:
    - `exportLogs(format)` function to handle the URL generation with current filters.

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-23 | - | Initial Plan Creation |
| 2026-01-23 | 1 | Plan Implemented (Export Logic, PDF View, Dropdown UI) |
