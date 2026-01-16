# Artifact: Edge Device List Troubleshooting
**Date:** 2026-01-16
**Revision Sequence:** -
**Reference Change:** -
**Context:** User reported issues with the Edge Device List loading: it required a refresh to display data, and navigating via the menu (SPA) resulted in an empty page.

## 1. Summary
The "Edge Devices" page in the dashboard was failing to load the device list under two specific conditions:
1.  **First Load:** The list was empty unless refreshed.
2.  **SPA Navigation:** Navigating from another module (e.g., Machines) to Edge Devices resulted in a broken page state (empty grid, 0 stats).

Both issues were traced to **Javascript Initialization Logic** and **SPA Configuration Mismatches**.

## 2. Key Decisions / Logic

### A. Initialization Timing (Race Condition)
-   **Problem:** `devices.js` was initializing immediately (`new DeviceManagement()`) before the DOM was fully parsed by the browser. This caused `document.getElementById('devices-grid')` to return null during the initial run.
-   **Decision:** Wrapped the initialization in a `DOMContentLoaded` check to ensure the DOM is ready.

### B. SPA Navigation Mismatch
-   **Problem:** The SPA Navigator (`spa-navigator.js`) uses a mapping object to determine which script to load/re-initialize based on the page name.
    -   Layout Link (`app.blade.php`) sent: `data-page="devices"`
    -   Navigator Map expected: `'edge-devices'`
-   **Result:** The navigator failed to find the mapping, so it never loaded or re-initialized `devices.js` when navigating via the menu.
-   **Decision:** Updated the Layout file to send `data-page="edge-devices"`.
-   **Safeguard:** Added a `pageLoaded` event listener in `devices.js` to catch SPA navigation events explicitly.

## 3. The Output (Code/Schema/Plan)

### Fix 1: Safe Initialization in `devices.js`
```javascript
// Safe Initialization Function
function initDeviceManagement() {
    if (window.deviceManagement) return; // Prevent double init
    window.deviceManagement = new DeviceManagement();
    // ...
}

// Check DOM State before initializing
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDeviceManagement);
} else {
    initDeviceManagement();
}
```

### Fix 2: Layout Attribute in `app.blade.php`
```html
<!-- Before -->
<a href="{{ route('dashboard.devices') }}" class="menu-link" data-page="devices">

<!-- After -->
<a href="{{ route('dashboard.devices') }}" class="menu-link" data-page="edge-devices">
```

### Fix 3: SPA Event Listener in `devices.js`
```javascript
// Handle SPA Navigation (re-init when coming from other pages)
document.addEventListener('pageLoaded', (e) => {
    if (e.detail && e.detail.page === 'edge-devices') {
        if (window.deviceManagement) {
            window.deviceManagement.setupEventListeners();
            window.deviceManagement.loadDevices();
        } else {
            initDeviceManagement();
        }
    }
});
```

## 4. Revision History Log
| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-16 | - | Initial Create: Documented fix for Device List Race Condition & SPA Navigation |
