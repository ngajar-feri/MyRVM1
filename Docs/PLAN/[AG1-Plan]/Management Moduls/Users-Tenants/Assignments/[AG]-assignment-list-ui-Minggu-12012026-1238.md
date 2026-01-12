# Rencana Implementasi Assignment List UI

**Versi Dokumen:** 1.0  
**Tanggal Revisi:** Minggu-12 Januari 2026 - 12:38 PM  
**Tujuan:** Implementasi halaman Assignment List dengan SPA navigation, Avatar initials, dan Status management  
**Status:** Ready for Implementation  
**Referensi:** [TR1 - Plan Assignment List UI](../../Users-Tenants/[TR1]-assignment-list-ui-design-Minggu-12Januari2026-0130.md)

---

## 1. Ringkasan

Dokumen ini merencanakan implementasi Assignment List UI sebagai halaman SPA yang dapat diakses dari tombol "Assignment" di halaman User Management. Fitur utama meliputi:

- **Assignment List Button**: Tombol baru di header User Management untuk navigasi ke Assignment List
- **Avatar Enhancement**: Menambahkan field avatar di tabel users dan menggunakan initials sebagai fallback
- **SPA Page**: Halaman terpisah dengan accordion-style table untuk assignment details
- **Status Management**: Action buttons untuk mengubah status assignments secara manual
- **Map Integration**: Integrasi dengan Google Maps untuk lokasi tasks

---

## 2. User Review Required

> [!IMPORTANT]
> **Breaking Changes**
> - Menambahkan field `avatar` ke tabel `users` - memerlukan migrasi database
> - Menambahkan route SPA baru `/dashboard/assignments` - memerlukan routing update

> [!WARNING]
> **Design Decisions**
> - Menggunakan initials (2 huruf) sebagai fallback untuk avatar kosong
> - Avatar akan disimpan sebagai path relatif (nullable)
> - Status update akan dilakukan secara optimistic (update UI immediately, rollback on error)

---

## 3. Proposed Changes

### Backend Changes

#### 3.1 Database Migration

##### [NEW] 2026_01_12_add_avatar_to_users_table.php

**Purpose**: Menambahkan kolom `avatar` ke tabel users  
**Changes**:
- Tambah kolom `avatar` (string, nullable) untuk menyimpan path avatar
- Tambah kolom `avatar_initials` (string, nullable) untuk cache initials

**Rationale**: Separate initials column untuk optimasi query dan fleksibilitas

---

#### 3.2 Model Updates

##### [MODIFY] User.php

**Changes**:
- Tambah `avatar` dan `avatar_initials` ke `$fillable`
- Tambah accessor `getAvatarUrlAttribute()` untuk generate full path avatar
- Tambah accessor `getInitialsAttribute()` untuk generate initials dari nama
- Tambah relationship `assignments()` untuk eager loading

---

#### 3.3 Controller Updates

##### [MODIFY] AssignmentController.php

**New Methods**:
- `updateStatus(Request $request, $id)`: Endpoint untuk update status assignment
  - Validasi: status must be in ['pending', 'in_progress', 'completed', 'cancelled']
  - Log status changes untuk audit trail
  - Return updated assignment dengan relationships

**Enhancement to Existing**:
- `index()`: Tambah eager loading untuk `user.avatar`, `machine`, `assignedBy`
- Add pagination support (15 per page by default)
- Add filtering by status, user, machine

---

#### 3.4 API Routes

##### [MODIFY] api.php

**Changes**:
```php
// Add new route for status update
Route::patch('/assignments/{id}/status', [AssignmentController::class, 'updateStatus']);
```

---

### Frontend Changes

#### 3.5 Blade Views

##### [NEW] index-assignments.blade.php

**Purpose**: Main Assignment List page  
**Structure**:
- Card dengan table assignment
- Hoverable rows dengan collapse untuk details
- Avatar group untuk team display
- Status badges dengan color coding
- Map button untuk open Google Maps
- Action dropdown untuk status update

**Features**:
- Bootstrap 5 accordion untuk row expansion
- Tooltips untuk avatar names
- Toast notification untuk map navigation
- Inline status update dengan dropdown

---

##### [MODIFY] index-content.blade.php (Users page)

**Changes**:
- Add "Assignment" button di header sebelah kiri "Add User" button
- Button styling: `btn-outline-primary` dengan icon `bx-task`
- SPA navigation ke `/dashboard/assignments`

---

#### 3.6 JavaScript Modules

##### [NEW] assignments.js

**Purpose**: Assignment List page logic  
**Key Functions**:
- `loadAssignments(page, filters)`: Fetch assignments via API
- `renderAssignments(data)`: Render assignment table rows
- `updateStatus(assignmentId, newStatus)`: Update assignment status
- `openLocation(lat, lng, name)`: Open Google Maps
- `toggleDetails(assignmentId)`: Toggle row collapse
- `initTooltips()`: Initialize Bootstrap tooltips

**Features**:
- Pagination handling
- Real-time status updates
- Error handling with toast notifications
- Optimistic UI updates

---

##### [MODIFY] spa-navigator.js

**Changes**:
- Add route handler untuk `/dashboard/assignments`
- Load assignments module on route change
- Update breadcrumb untuk assignment page

---

#### 3.7 CSS Styling

##### [NEW] assignments.css

**Purpose**: Assignment-specific styles  
**Includes**:
- Avatar group styling dengan pull-up effect
- Cursor pointer untuk clickable rows
- Collapsed row background
- Status badge custom colors
- Avatar initials styling (circular badge)

---

### Component Implementation

#### 3.8 Avatar Initials Component

**Implementation Strategy**:
1. Backend generates initials on user creation/update
2. Frontend displays initials as circular badge if no avatar
3. Background color based on user ID hash for consistency
4. Tooltip shows full name on hover

**Example Output**:
```html
<div class="avatar avatar-sm">
    <span class="avatar-initial rounded-circle bg-primary">FF</span>
</div>
```

---

## 4. Verification Plan

### 4.1 Database Migration Testing

**Test Command**:
```bash
docker exec myrvm-app php artisan migrate
docker exec myrvm-app php artisan tinker --execute="echo User::first()->avatar_initials;"
```

**Expected Result**: Migration successful, avatar fields exist

---

### 4.2 API Testing

**Test Endpoints**:
1. GET `/api/v1/admin/assignments` - List all assignments
2. PATCH `/api/v1/admin/assignments/{id}/status` - Update status

**Test Command** (via browser console):
```javascript
// Test assignment list
await APIHelper.get('/api/v1/admin/assignments');

// Test status update
await APIHelper.patch('/api/v1/admin/assignments/1/status', { status: 'in_progress' });
```

**Expected Result**: 
- List returns assignments with user avatars && initials
- Status update returns 200 with updated assignment

---

### 4.3 Browser E2E Testing

**Manual Test Steps**:
1. Navigate to `http://localhost:8000/dashboard/users`
2. Verify "Assignment" button appears next to "Add User"
3. Click "Assignment" button
4. Verify navigation to Assignment List page
5. Verify assignment rows display with:
   - Team avatars (initials if no image)
   - Status badges
   - Map button
   - Action dropdown
6. Click a row to expand details
7. Click Map button → verify Google Maps opens
8. Click Action → Update Status → verify status changes
9. Verify toast notification appears on status update

**Screenshot Points**:
- Assignment button on Users page
- Assignment List page
- Expanded row details
- Status update in action
- Avatar initials display

---

### 4.4 Avatar Initials Testing

**Test Steps**:
1. Create user without avatar
2. Verify initials are generated (First + Last)
3. Verify initials display in Assignment team
4. Update user name → verify initials update
5. Upload avatar → verify image replaces initials

---

## 5. Implementation Sequence

1. **Phase 1: Database** (15 min)
   - Create and run migration for avatar fields
   - Update User model with accessors

2. **Phase 2: Backend API** (30 min)
   - Add updateStatus endpoint
   - Enhance index method with eager loading
   - Test API endpoints

3. **Phase 3: Frontend Structure** (45 min)
   - Create assignments.blade.php
   - Create assignments.css
   - Add Assignment button to users page

4. **Phase 4: JavaScript Logic** (60 min)
   - Create assignments.js module
   - Implement CRUD operations
   - Add status update logic
   - Implement map integration

5. **Phase 5: Integration** (30 min)
   - Wire up SPA navigation
   - Test end-to-end flow
   - Fix any styling issues

6. **Phase 6: Verification** (30 min)
   - Run all verification tests
   - Capture screenshots
   - Document issues

**Total Estimated Time**: 3.5 hours

---

## 6. Rollback Plan

If critical issues arise:

1. **Database Rollback**:
   ```bash
   docker exec myrvm-app php artisan migrate:rollback --step=1
   ```

2. **Frontend Rollback**:
   - Remove Assignment button from users page
   - Remove route from spa-navigator.js
   - Delete assignments.js and assignments.css

3. **API Rollback**:
   - Comment out updateStatus route
   - Revert AssignmentController changes

**Validation**: User management page should function as before

---

## 7. Post-Implementation Tasks

- [ ] Update API documentation
- [ ] Create user guide for Assignment management
- [ ] Add unit tests for updateStatus endpoint
- [ ] Performance testing with 100+ assignments
- [ ] Mobile responsiveness verification
- [ ] Create walkthrough documentation

---

## 8. Notes

- Avatar upload functionality can be added in future iteration
- Consider adding real-time updates via WebSocket/Pusher for status changes
- Map integration could be enhanced with embedded maps instead of external links
- Status history/audit log could be valuable for tracking progress

