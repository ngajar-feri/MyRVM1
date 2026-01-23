# Artifact: Fix PDF Export Issue in Activity Logs
**Date:** 2026-01-23
**Revision Sequence:** -
**Reference Change:** -
**Context:** User reported issues with PDF export fail due to authentication (missing Bearer token in window.open) and Docker missing dependencies (GD/Freetype).
**Status:** Verified (Download Successful)

## 1. Summary
Implemented a comprehensive fix for the Export to PDF/Excel functionality in the Activity Logs module. The fix addresses authentication failures by switching from `window.open` to `fetch` with Bearer tokens, resolves missing system dependencies in Docker, and configures Laravel/DomPDF for proper PDF generation.

## 2. Key Decisions / Logic
- **Authentication Handling:** Replaced `window.open()` with `fetch()` + `Blob` to allow sending the `Authorization: Bearer` header, fixing 401 Unauthorized errors.
- **Docker Environment:** Added `libfreetype6-dev` and `libjpeg62-turbo-dev` to `Dockerfile` and configured `gd` extensions to support image rendering in PDFs.
- **Font Management:** Added a composer script loop to ensure `storage/fonts` exists and is writable, preventing permission errors relative to DomPDF.
- **Memory & Time Limits:** Increased `memory_limit` (256M) and `max_execution_time` (300s) specifically for the PDF export route to handle larger datasets.
- **Provider Auto-discovery:** Reverted manual `ServiceProvider` registration in `bootstrap/providers.php` to align with Laravel 11/12 auto-discovery standards.

## 3. The Output (Code/Schema/Plan)

### Frontend (logs.js) - `exportLogs` Method
```javascript
async exportLogs(format = 'excel') {
    try {
        // ... loading UI code ...

        const params = new URLSearchParams({ ...this.filters, per_page: 1000, format: format });
        const token = localStorage.getItem('token'); 

        const response = await fetch(`/api/v1/logs/export?${params}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf',
            }
        });

        if (!response.ok) throw new Error('Export failed');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        // ... download trigger code ...
        
    } catch (error) {
        console.error('Export error:', error);
        alert('Gagal mengunduh file: ' + error.message);
    } 
}
```

### Backend (LogController.php)
```php
if ($request->input('format') === 'pdf') {
    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', 300);

    $pdf = Pdf::loadView('dashboard.logs.pdf', ['logs' => $logs]);
    $pdf->setPaper('a4', 'landscape');
    $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

    return $pdf->download('activity_logs_' . date('Y-m-d_H-i') . '.pdf');
}
```

### Dockerfile Changes
```dockerfile
RUN apt-get update && apt-get install -y ... libfreetype6-dev libjpeg62-turbo-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install ... gd
```

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-23 | - | Initial Fix Implementation |
