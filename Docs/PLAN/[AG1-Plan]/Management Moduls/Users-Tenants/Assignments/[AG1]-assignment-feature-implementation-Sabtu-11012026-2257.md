**Versi Dokumen:** 1.0  
**Tanggal Revisi:** Sabtu-11 Januari 2026 - 10:57 PM  
**Tujuan:** Mendokumentasikan rencana pengembangan fitur Assignment untuk penugasan instalasi RVM Machines dengan tag-based selection, notifikasi otomatis, dan peta interaktif dengan geocoding.  
**Status:** Belum

---

# [AG1] Assignment Feature - RVM Installation Management

## 1. Executive Summary

Fitur ini memungkinkan Super Admin, Admin, dan Teknisi untuk menugaskan personel ke RVM Machines untuk instalasi dengan pencatatan lokasi geografis melalui peta interaktif. Sistem akan mengirimkan notifikasi email dan in-app kepada teknisi yang ditugaskan.

### Key Features
- ✅ Tag-based user/machine selection (email CC-style)
- ✅ Email & in-app notifications
- ✅ Interactive map dengan geocoding search
- ✅ Draggable marker untuk precision
- ✅ Reverse geocoding untuk alamat otomatis

---

## 2. Scope & Requirements

### 2.1 Functional Requirements

#### FR-01: Multi-Assignment Creation
- **Requirement:** User dapat menugaskan multiple teknisi ke multiple RVM machines dalam satu aksi
- **Business Rule:** Setiap kombinasi user-machine akan menjadi 1 assignment record
- **Example:** 2 users + 3 machines = 6 assignment records
- **Priority:** HIGH

#### FR-02: Role-Based Authorization
- **Requirement:** Hanya role tertentu yang dapat membuat assignment
- **Allowed Roles:** super_admin, admin, operator, teknisi
- **Business Rule:** Teknisi/Operator hanya bisa assign ke dirinya sendiri
- **Priority:** HIGH

#### FR-03: Location Capture
- **Requirement:** Sistem dapat menangkap dan menyimpan koordinat geografis
- **Input Methods:**
  - Klik pada peta
  - Search location by name (geocoding)
  - Drag marker untuk adjust
- **Output:** Latitude, Longitude, Address
- **Priority:** HIGH

#### FR-04: Automated Notifications
- **Requirement:** Sistem mengirim notifikasi otomatis saat assignment dibuat
- **Channels:**
  - Email (queued)
  - In-app notification (database)
- **Content:** Machine name, location, assigned by, deadline
- **Priority:** MEDIUM

#### FR-05: Tag-Based Selection UI
- **Requirement:** User interface untuk selection harus menggunakan tag/chip pattern
- **Behavior:**
  - User ketik nama → autocomplete muncul
  - Klik item → jadi chip/tag
  - Klik X pada tag → remove from selection
- **Priority:** MEDIUM

---

### 2.2 Non-Functional Requirements

#### NFR-01: Performance
- **Response Time:** Assignment creation < 2 detik
- **Map Loading:** Peta harus terload < 3 detik
- **Geocoding:** Search result < 1.5 detik
- **Target:** 95th percentile

#### NFR-02: Usability
- **Mobile Responsive:** Modal harus responsive di mobile
- **Accessibility:** Map controls harus accessible via keyboard
- **Browser Support:** Chrome 90+, Firefox 88+, Safari 14+

#### NFR-03: Reliability
- **Uptime:** 99.5% availability
- **Error Handling:** Graceful degradation jika geocoding API down
- **Fallback:** Manual lat/lng input jika map gagal load

---

## 3. Technical Architecture

### 3.1 Database Schema

```sql
CREATE TABLE assignments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    machine_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    address VARCHAR(500) NULL,
    notes TEXT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    UNIQUE KEY unique_assignment (user_id, machine_id, assigned_at),
    INDEX idx_status_date (status, assigned_at)
);
```

### 3.2 API Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/admin/assignments` | Create bulk assignments | Yes |
| GET | `/api/v1/admin/assignments` | List all assignments | Yes |
| GET | `/api/v1/admin/assignments/{id}` | Get assignment detail | Yes |
| PUT | `/api/v1/admin/assignments/{id}` | Update assignment status | Yes |
| DELETE | `/api/v1/admin/assignments/{id}` | Cancel assignment | Yes |
| GET | `/api/v1/admin/assignments/user/{userId}` | Get user's assignments | Yes |
| GET | `/api/v1/admin/assignments/machine/{machineId}` | Get machine's assignments | Yes |

### 3.3 Component Architecture

```
Frontend Components:
├── TagAutocomplete.js          // Reusable tag-based selection
├── EnhancedMapHandler.js       // Map with geocoding
├── users.js (modified)          // User management module
└── tag-autocomplete.css        // Styling for tags

Backend Components:
├── Assignment.php               // Eloquent model
├── AssignmentController.php    // API controller
├── AssignmentCreated.php       // Notification class
└── AssignmentPolicy.php        // Authorization policy

External Dependencies:
├── Leaflet.js (v1.9.4)         // Map rendering
├── Nominatim API               // Geocoding (free)
└── OpenStreetMap Tiles         // Map tiles (free)
```

---

## 4. Implementation Plan

### 4.1 Phase 1: Backend Foundation (Est. 3-4 jam)

**Tasks:**
- [ ] Create migration for `assignments` table
- [ ] Create `Assignment` model with relationships
- [ ] Create `AssignmentController` with CRUD endpoints
- [ ] Implement authorization policy
- [ ] Create `AssignmentCreated` notification class
- [ ] Add routes to `api.php`
- [ ] Write unit tests for controller

**Deliverables:**
- Migration file
- Model class
- Controller class
- Notification class
- Route configuration

---

### 4.2 Phase 2: Frontend Components (Est. 4-5 jam)

**Tasks:**
- [ ] Create `TagAutocomplete` component
- [ ] Create `EnhancedMapHandler` component
- [ ] Add modal HTML to `index-content.blade.php`
- [ ] Update `users.js` with assignment methods
- [ ] Create `tag-autocomplete.css`
- [ ] Add Leaflet.js CDN to layout
- [ ] Add "Assignment" button to user table

**Deliverables:**
- JavaScript components
- Modal UI
- CSS styling
- Integration code

---

### 4.3 Phase 3: Integration & Testing (Est. 2-3 jam)

**Tasks:**
- [ ] Test tag autocomplete functionality
- [ ] Test geocoding search
- [ ] Test draggable marker
- [ ] Test reverse geocoding
- [ ] Test notification sending
- [ ] Test authorization rules
- [ ] Cross-browser testing
- [ ] Mobile responsive testing

**Deliverables:**
- Test report
- Bug fixes
- Performance optimization

---

## 5. Testing Strategy

### 5.1 Unit Tests

```php
// tests/Unit/AssignmentTest.php
class AssignmentTest extends TestCase
{
    /** @test */
    public function it_creates_bulk_assignments()
    {
        $response = $this->postJson('/api/v1/admin/assignments', [
            'user_ids' => [1, 2],
            'machine_ids' => [1, 2, 3],
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'address' => 'Jakarta',
            'notes' => 'Test assignment'
        ]);
        
        $response->assertStatus(201);
        $this->assertCount(6, $response->json('assignments'));
    }
    
    /** @test */
    public function it_sends_notification_to_assigned_user()
    {
        Notification::fake();
        
        // Create assignment...
        
        Notification::assertSentTo($user, AssignmentCreated::class);
    }
}
```

### 5.2 Integration Tests

**Test Case 1: Tag Autocomplete**
- [ ] User types "John" → autocomplete shows matching users
- [ ] User clicks "John Doe" → tag chip appears
- [ ] User clicks X on tag → tag removed
- [ ] Selected users excluded from autocomplete results

**Test Case 2: Map Geocoding**
- [ ] User searches "Jogja City Mall" → map zooms to location
- [ ] User clicks map → marker placed, coordinates captured
- [ ] User drags marker → coordinates updated
- [ ] Address field auto-populated via reverse geocoding

**Test Case 3: Assignment Creation**
- [ ] User selects 2 users, 3 machines → 6 assignments created
- [ ] Database records created correctly
- [ ] Email notifications queued
- [ ] In-app notifications created
- [ ] Success message displayed

**Test Case 4: Authorization**
- [ ] Super Admin can assign anyone to any machine ✅
- [ ] Admin can assign anyone to any machine ✅
- [ ] Teknisi can only assign self ✅
- [ ] Regular User cannot access assignment ❌

---

### 5.3 Walkthrough Testing Plan

#### Pre-requisites
- Staging environment with fresh database
- Test users dengan roles: super_admin, admin, teknisi, user
- Test machines: minimal 3 RVM machines
- Email testing tool (Mailtrap/MailHog)

#### Walkthrough Steps

**Step 1: Login & Navigation**
1. Login sebagai Super Admin (`admin@myrvm.com`)
2. Navigate to "User & Tenants" page
3. **Expected:** Page loads successfully, user table displayed
4. **Screenshot:** Capture user table view

**Step 2: Open Assignment Modal**
1. Click menu dropdown pada user "Teknisi 001"
2. Click "Assignment" button
3. **Expected:** Modal opens dengan title "RVM Installation Assignment"
4. **Screenshot:** Capture modal UI

**Step 3: Tag-Based User Selection**
1. Click pada "Assign To" input field
2. Type "tek" 
3. **Expected:** Autocomplete shows users dengan nama containing "tek"
4. Click "Teknisi 001" dari suggestions
5. **Expected:** Tag chip "Teknisi 001" muncul di tag list
6. Type "oper"
7. Click "Operator RVM"
8. **Expected:** Tag chip "Operator RVM" muncul
9. **Screenshot:** Capture tag chips

**Step 4: Machine Selection**
1. Click pada "RVM Machines" input field
2. Type "rvm"
3. Click "RVM-001" dan "RVM-002" dari suggestions
4. **Expected:** 2 tag chips untuk machines muncul
5. **Screenshot:** Capture machine tags

**Step 5: Location Search (Geocoding)**
1. Type "Jogja City Mall" di search bar
2. Click "Search" button
3. **Expected:** 
   - Map zooms ke Yogyakarta
   - Marker placed di Jogja City Mall
   - Latitude/Longitude fields populated
   - Address field shows "Jogja City Mall, Yogyakarta, ..."
4. **Screenshot:** Capture map with marker and coordinates

**Step 6: Adjust Location (Draggable Marker)**
1. Drag marker ke lokasi sedikit berbeda
2. **Expected:**
   - Coordinates update saat drag selesai
   - Address field update via reverse geocoding
3. **Screenshot:** Capture updated coordinates

**Step 7: Add Notes**
1. Type "Urgent installation required" di Notes field
2. **Expected:** Notes accepted
3. **Screenshot:** Capture complete form

**Step 8: Submit Assignment**
1. Click "Create Assignment" button
2. **Expected:**
   - Success message appears: "✅ 4 assignments created and notifications sent"
   - Modal closes
3. **Screenshots:**
   - Success message
   - Updated user table (if assignments visible)

**Step 9: Verify Database**
```sql
SELECT 
    a.id,
    u.name as teknisi,
    m.name as machine,
    a.latitude,
    a.longitude,
    a.address,
    a.status,
    ab.name as assigned_by
FROM assignments a
JOIN users u ON a.user_id = u.id
JOIN machines m ON a.machine_id = m.id  
JOIN users ab ON a.assigned_by = ab.id
ORDER BY a.created_at DESC
LIMIT 10;
```
4. **Expected:** 4 records (2 users × 2 machines)
5. **Screenshot:** Query results

**Step 10: Verify Email Notification**
1. Check Mailtrap inbox for "Teknisi 001" email
2. **Expected:**
   - Subject: "🔧 New RVM Installation Assignment"
   - Body contains: machine name, location, coordinates
   - "View Assignment" button present
3. **Screenshot:** Email preview

**Step 11: Verify In-App Notification**
1. Login as "Teknisi 001"
2. Click notification bell icon
3. **Expected:** Notification appears "You have been assigned to install RVM-001"
4. **Screenshot:** Notification dropdown

---

### 5.4 Performance Testing

**Load Test Scenario:**
- **Concurrent Users:** 50 users
- **Operations:** Create assignments simultaneously
- **Duration:** 5 minutes
- **Expected Response Time:** < 2 seconds (95th percentile)
- **Expected Error Rate:** < 1%

**Geocoding API Test:**
- **Rate Limit:** Nominatim = 1 request/second per IP
- **Mitigation:** Implement client-side debouncing (500ms)
- **Fallback:** Manual lat/lng input jika API timeout

---

## 6. Deployment Strategy

### 6.1 Pre-Deployment Checklist

- [ ] All unit tests passing (100% coverage for critical paths)
- [ ] Integration tests passing
- [ ] Manual walkthrough completed and documented
- [ ] Database migration reviewed and tested
- [ ] Notification queue configured correctly
- [ ] Environment variables set (if any)
- [ ] Performance benchmarks met
- [ ] Security audit completed
- [ ] Code review approved by senior developer
- [ ] Documentation updated

---

### 6.2 Deployment Steps (Staging)

**Step 1: Database Migration**
```bash
# Backup database
php artisan db:backup

# Run migration
php artisan migrate

# Verify migration
php artisan migrate:status
```

**Step 2: Deploy Code**
```bash
# Pull latest code
git pull origin develop

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

**Step 3: Verify Deployment**
```bash
# Check application health
php artisan health:check

# Test critical endpoints
curl -X POST https://staging.myrvm.com/api/v1/admin/assignments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"user_ids":[1],"machine_ids":[1]}'
```

**Step 4: Monitor**
- [ ] Check Laravel logs for errors
- [ ] Monitor queue workers
- [ ] Verify email delivery
- [ ] Check database query performance

---

### 6.3 Deployment Steps (Production)

**Prerequisites:**
- [ ] Staging deployment successful for 48 hours
- [ ] No critical bugs reported
- [ ] Stakeholder approval obtained

**Deployment Window:** Saturday 02:00 AM - 04:00 AM (Low traffic period)

**Steps:**
1. **Pre-deployment**
   ```bash
   # Announce maintenance window
   php artisan down --message="Upgrading Assignment System" --retry=60
   
   # Full database backup
   mysqldump -u root -p myrvm_prod > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Deploy**
   ```bash
   # Deploy code (same as staging)
   git pull origin main
   composer install --no-dev --optimize-autoloader
   npm run build
   
   # Run migrations
   php artisan migrate --force
   
   # Clear caches
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Verify**
   ```bash
   # Smoke tests
   php artisan test --filter=AssignmentTest
   
   # Health check
   php artisan health:check
   ```

4. **Go Live**
   ```bash
   # Bring application back online
   php artisan up
   
   # Monitor logs in real-time
   tail -f storage/logs/laravel.log
   ```

---

## 7. Rollback Plan

### 7.1 Rollback Triggers

**Immediate Rollback:**
- Critical bug discovered (P0 severity)
- Data corruption detected
- Performance degradation > 50%
- Security vulnerability identified

**Scheduled Rollback:**
- High volume of user complaints
- Feature not working as designed
- Unexpected behavior in core functionality

---

### 7.2 Rollback Procedures

**Option 1: Code Rollback (Fastest - ~5 minutes)**

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Revert to previous git commit
git revert <commit-hash>
git push origin main

# 3. Redeploy previous version
composer install --no-dev
npm run build

# 4. Clear caches
php artisan optimize:clear

# 5. Bring application online
php artisan up
```

**Option 2: Database Rollback (If migration caused issues - ~15 minutes)**

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Rollback migration
php artisan migrate:rollback --step=1

# 3. Verify database state
php artisan migrate:status

# 4. Restore code (if needed)
git revert <commit-hash>

# 5. Bring application online
php artisan up
```

**Option 3: Full Restore (Worst case - ~30 minutes)**

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Restore database from backup
mysql -u root -p myrvm_prod < backup_YYYYMMDD_HHMMSS.sql

# 3. Restore code from previous tag
git checkout v1.2.3  # Previous stable version
composer install --no-dev
npm run build

# 4. Clear all caches
php artisan optimize:clear

# 5. Verify application
php artisan test

# 6. Bring application online
php artisan up
```

---

### 7.3 Post-Rollback Actions

**Immediate:**
- [ ] Notify stakeholders of rollback
- [ ] Document rollback reason and timestamp
- [ ] Create incident report
- [ ] Preserve logs for analysis

**Short-term (within 24 hours):**
- [ ] Root cause analysis meeting
- [ ] Identify fix for original issue
- [ ] Update test cases to catch missed scenario
- [ ] Plan for re-deployment

---

## 8. Risk Assessment

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|--------|---------------------|
| Geocoding API rate limit exceeded | Medium | Low | Implement client-side debouncing + fallback manual input |
| Email notifications fail | Low | Medium | Use queue with retry mechanism + monitor queue health |
| Map fails to load | Low | Low | Graceful degradation + manual lat/lng input |
| Database migration fails | Low | High | Test in staging + have rollback ready |
| Performance degradation | Medium | Medium | Load testing before deploy + monitoring |
| Authorization bypass | Low | Critical | Security audit + automated policy tests |

---

## 9. Monitoring & Metrics

### 9.1 Application Metrics

**Track via Laravel Telescope/Horizon:**
- Assignment creation rate (per hour)
- API response times (p50, p95, p99)
- Queue job processing time
- Failed job rate
- Database query performance

**Custom Metrics:**
```php
// In AssignmentController
Metrics::increment('assignments.created', [
    'user_count' => count($userIds),
    'machine_count' => count($machineIds)
]);

Metrics::timing('assignments.geocoding_duration', $duration);
```

---

### 9.2 Business Metrics

**Daily Reports:**
- Total assignments created
- Assignments by status (pending/in_progress/completed)
- Average completion time
- Most assigned machines
- Most assigned technicians

**Weekly Reports:**
- Assignment completion rate
- Email delivery success rate
- User engagement (% of users using assignment feature)

---

## 10. Changelog

### Version 1.0 (2026-01-11)

**Added:**
- Assignment creation functionality dengan multi-user & multi-machine support
- Tag-based autocomplete untuk user/machine selection
- Interactive map dengan Leaflet.js + OpenStreetMap
- Geocoding search menggunakan Nominatim API
- Draggable marker untuk location precision
- Reverse geocoding untuk auto-populate address
- Email notification via Laravel Mail
- In-app notification via database channel
- Role-based authorization policy

**Database Changes:**
- Created `assignments` table
- Added indexes for performance

**API Endpoints:**
- `POST /api/v1/admin/assignments` - Create assignments
- `GET /api/v1/admin/assignments` - List assignments
- `PUT /api/v1/admin/assignments/{id}` - Update assignment
- `DELETE /api/v1/admin/assignments/{id}` - Cancel assignment

**Frontend Components:**
- `TagAutocomplete.js` - Reusable tag selection component
- `EnhancedMapHandler.js` - Map wrapper dengan geocoding
- Assignment modal UI
- Tag autocomplete CSS styling

**Dependencies:**
- Leaflet.js v1.9.4
- Nominatim API (OpenStreetMap)

---

## 11. Documentation Links

**Technical Documentation:**
- API Documentation: `/docs/api/assignments`
- Database Schema: `/docs/database/assignments-table`
- Authorization Policy: `/docs/security/assignment-policy`

**User Documentation:**
- User Guide: `/docs/user-guide/assignments`
- Admin Guide: `/docs/admin-guide/assignment-management`
- FAQ: `/docs/faq/assignments`

---

## 12. Support & Maintenance

**Point of Contact:**
- **Developer:** [Your Name]
- **Tech Lead:** [Tech Lead Name]
- **Product Owner:** [PO Name]

**Support Channels:**
- Bug Reports: GitHub Issues
- Feature Requests: Product Backlog
- Emergency Contact: [Emergency Phone/Email]

**Maintenance Schedule:**
- Weekly health check: Every Monday 09:00 AM
- Monthly performance review: First Friday of month
- Quarterly feature review: End of quarter

---

## 13. Approval Sign-off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Developer | __________ | __________ | __/__/__ |
| Tech Lead | __________ | __________ | __/__/__ |
| Product Owner | __________ | __________ | __/__/__ |
| QA Lead | __________ | __________ | __/__/__ |

---

**Document End**

*Last Updated: 2026-01-11 22:57 PM*  
*Next Review: 2026-01-18*
