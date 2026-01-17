# Artifact: Edge Device CRUD Bug Fixes
**Date:** 2026-01-17
**Revision Sequence:** -
**Reference Change:** -
**Context:** User reported 2 bugs after implementing Edge Device CRUD features

---

## 1. Summary

Dua bug ditemukan setelah implementasi fitur CRUD Edge Device:
1. **Tombol "Hapus ke Trash" memudar** - warna merah semakin pucat setelah beberapa kali delete
2. **Toast notification tertutup navbar** - notifikasi sukses tidak terlihat dengan benar

---

## 2. Bug Analysis & Root Cause

### BUG 1: Modal Button Color Fading

**Symptoms:**
- Tombol "Hapus ke Trash" berubah dari merah → pink → putih
- Terjadi setelah multiple delete operations

**Root Cause:**
Bootstrap modal backdrop (layer gelap di belakang modal) **terakumulasi** setiap kali modal dibuka. Setiap backdrop menambah layer opacity, sehingga warna tombol terlihat semakin pudar.

**Evidence (Screenshots):**
- Total Device: 13 → 11 → 8 → 5 (semakin sedikit, tombol semakin pucat)

````carousel
![First delete - button normal](file:///C:/Users/Server/.gemini/antigravity/brain/24c3b66a-0f32-4462-a6e2-34893ac82aed/uploaded_image_1_1768637326298.png)
<!-- slide -->
![Multiple deletes - button fading](file:///C:/Users/Server/.gemini/antigravity/brain/24c3b66a-0f32-4462-a6e2-34893ac82aed/uploaded_image_3_1768637326298.png)
<!-- slide -->
![Many deletes - button almost white](file:///C:/Users/Server/.gemini/antigravity/brain/24c3b66a-0f32-4462-a6e2-34893ac82aed/uploaded_image_4_1768637326298.png)
````

---

### BUG 2: Toast Hidden Behind Navbar

**Symptoms:**
- Notifikasi "Success" muncul di pojok kanan atas
- Sebagian tertutup oleh fixed navbar

**Root Cause:**
Toast container tidak memiliki `z-index` yang cukup tinggi dan tidak ada `margin-top` untuk memperhitungkan tinggi navbar.

---

## 3. Solutions Implemented

### Fix 1: Cleanup Modal Backdrops

**File:** [devices.js](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/public/js/modules/devices.js)

Added new method `cleanupModalBackdrops()`:

```javascript
cleanupModalBackdrops() {
    setTimeout(() => {
        // Remove all stale modal backdrops
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.remove();
        });
        // Remove modal-open class from body if no modals are shown
        if (!document.querySelector('.modal.show')) {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    }, 300); // Wait for modal hide animation
}
```

**Called after:**
- `deleteDevice()` - after modal hide

---

### Fix 2: Toast Container Positioning

**File:** [devices.js](file:///d:/~dev/MyReverseVendingMachine1/MySuperApps/MyRVM-Server/public/js/modules/devices.js)

Updated `showToast()`:

```javascript
container.style.zIndex = '1090'; // Above navbar (1050)
container.style.marginTop = '60px'; // Account for navbar height
```

---

## 4. Related Changes (From Earlier Session)

| Change | Purpose |
|--------|---------|
| `getOrCreateInstance()` | Prevent creating multiple modal instances |
| `cleanupModalBackdrops()` | Remove stale backdrops after modal close |
| Toast z-index & margin | Ensure visibility above navbar |

---

## 5. Testing Instructions

1. Hard refresh browser (`Ctrl+F5`)
2. Delete 5+ devices consecutively
3. Verify button color stays **consistently red**
4. Verify toast notification appears **below navbar, fully visible**

---

## 4. Revision History Log

| Date | Rev | Change Notes |
| :--- | :--- | :--- |
| 2026-01-17 | - | Initial documentation of CRUD bug fixes |
