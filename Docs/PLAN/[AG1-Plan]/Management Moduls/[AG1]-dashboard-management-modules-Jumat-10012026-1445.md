# [AG1] Dashboard Management Modules - Development Plan

**Versi Dokumen**: 1.0  
**Tanggal Revisi**: Jumat-10 Januari 2026 - 02:45 PM  
**Tujuan**: Comprehensive development plan untuk implementasi Management Modules pada Dashboard MyRVM-Server dengan SPA pattern, modern UI/UX, dan full responsiveness  
**Status**: Belum

---

## 📋 RINGKASAN EKSEKUTIF

### Scope of Work
Pengembangan 4 module management utama untuk Dashboard MyRVM-Server:
1. **User & Tenants Management**
2. **RVM Machines Management**
3. **Edge Devices Management**
4. **CV Servers Management**

### Technical Approach
- **Architecture**: Single Page Application (SPA) dengan AJAX/Fetch API
- **Frontend Framework**: Blade Templates + Vanilla JavaScript  
- **UI Framework**: Bootstrap 5 (existing) dengan custom components
- **Charts**: ApexCharts (already included)
- **Pattern**: RESTful API + Dynamic Content Loading

---

## 🎯 OBJECTIVES

### Primary Goals
1. ✅ Implementasi 4 management modules lengkap
2. ✅ SPA navigation tanpa full page reload
3. ✅ Responsive design (mobile-first approach)
4. ✅ Modern UI/UX dengan microinteractions
5. ✅ Real-time data updates
6. ✅ Role-based access control (RBAC)

### Success Metrics
- Lighthouse Score: >90
- Page Load Time: <2s
- Time to Interactive: <3s
- Accessibility Score: WCAG 2.1 AA
- Mobile Responsiveness: 320px - 1920px+

---

## 📐 ARCHITECTURE OVERVIEW

### Current Structure Analysis

```
resources/views/
├── layouts/
│   └── app.blade.php          ← Main layout (navbar, sidebar, footer static)
├── dashboard/
│   ├── home.blade.php         ← Existing dashboard home
│   └── api-docs.blade.php     ← Existing API docs page
```

### Proposed Structure

```
resources/views/
├── layouts/
│   └── app.blade.php          ← Enhanced with SPA support
├── dashboard/
│   ├── home.blade.php         ← Main dashboard (enhanced)
│   ├── api-docs.blade.php     ← Existing
│   ├── users/
│   │   ├── index.blade.php    ← User & Tenants management
│   │   ├── create.blade.php   ← Create user modal
│   │   └── edit.blade.php     ← Edit user modal
│   ├── machines/
│   │   ├── index.blade.php    ← RVM Machines list
│   │   ├── detail.blade.php   ← Machine detail view
│   │   └── map.blade.php      ← Geographic map view
│   ├── devices/
│   │   ├── index.blade.php    ← Edge Devices list
│   │   ├── detail.blade.php   ← Device detail + telemetry
│   │   └── monitoring.blade.php ← Real-time monitoring
│   └── cv-servers/
│       ├── index.blade.php    ← CV Servers list
│       ├── detail.blade.php   ← Server detail
│       └── training.blade.php ← Training jobs monitor
```

---

## 🔧 TECHNICAL SPECIFICATIONS

### 1. SPA Navigation Pattern

#### Implementation Strategy
```javascript
// public/js/spa-navigation.js

class SPANavigator {
    constructor() {
        this.contentContainer = document.querySelector('.container-xxl.flex-grow-1');
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Intercept menu clicks
        document.querySelectorAll('.menu-link[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage(e.target.dataset.page);
            });
        });
        
        // Browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        });
    }
    
    async loadPage(pageName, pushState = true) {
        try {
            // Show loading state
            this.showLoading();
            
            // Fetch page content
            const response = await fetch(`/dashboard/${pageName}/content`);
            const html = await response.text();
            
            // Update content
            this.contentContainer.innerHTML = html;
            
            // Update active menu
            this.updateActiveMenu(pageName);
            
            // Push history state
            if (pushState) {
                history.pushState({page: pageName}, '', `/dashboard/${pageName}`);
            }
            
            // Initialize page-specific scripts
            this.initPageScripts(pageName);
            
        } catch (error) {
            this.showError(error);
        } finally {
            this.hideLoading();
        }
    }
    
    showLoading() {
        // Skeleton loader or spinner
    }
    
    hideLoading() {
        // Remove loader
    }
    
    updateActiveMenu(pageName) {
        // Update menu active state
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.spaNavigator = new SPANavigator();
});
```

### 2. Menu Link Updates

#### Updated app.blade.php (Navigation)
```html
<!-- Management Modules -->
<li class="menu-item">
    <a href="/dashboard/users" class="menu-link" data-page="users">
        <i class="menu-icon icon-base ti tabler-users"></i>
        <div>User & Tenants</div>
    </a>
</li>
<li class="menu-item">
    <a href="/dashboard/machines" class="menu-link" data-page="machines">
        <i class="menu-icon icon-base ti tabler-device-desktop-analytics"></i>
        <div>RVM Machines</div>
    </a>
</li>
<li class="menu-item">
    <a href="/dashboard/devices" class="menu-link" data-page="devices">
        <i class="menu-icon icon-base ti tabler-cpu"></i>
        <div>Edge Devices</div>
    </a>
</li>
<li class="menu-item">
    <a href="/dashboard/cv-servers" class="menu-link" data-page="cv-servers">
        <i class="menu-icon icon-base ti tabler-server"></i>
        <div>CV Servers</div>
    </a>
</li>
```

---

## 📦 MODULE 1: USER & TENANTS MANAGEMENT

### Features
1. **User List Table**
   - Sortable columns (name, email, role, points, date)
   - Search/filter (role, status, date range)
   - Pagination (10/25/50/100 per page)
   - Bulk actions (activate/deactivate, delete)

2. **User Details View**
   - Profile information
   - Points history chart
   - Transaction summary
   - Voucher redemptions
   - Activity log

3. **Create/Edit User**
   - Form validation (client + server)
   - Role selection (user, admin, tenant, technician)
   - Photo upload with preview
   - Points manual adjustment (admin only)

4. **Tenant Management**
   - Tenant-specific dashboard
   - Voucher management integration
   - Analytics (redemptions, revenue)

### Data Sources (API Endpoints)
```
GET    /api/v1/admin/users              ← List users (paginated)
GET    /api/v1/admin/users/{id}         ← User detail
POST   /api/v1/admin/users              ← Create user
PUT    /api/v1/admin/users/{id}         ← Update user
DELETE /api/v1/admin/users/{id}         ← Delete user
GET    /api/v1/admin/users/{id}/stats   ← User statistics
```

### UI Components

#### User List Table
```html
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">User Management</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="ti tabler-plus me-1"></i> Add User
        </button>
    </div>
    <div class="card-body">
        <!-- Search & Filter -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Search users...">
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option>All Roles</option>
                    <option>Admin</option>
                    <option>User</option>
                    <option>Tenant</option>
                    <option>Technician</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100">Filter</button>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input"></th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- Dynamic content via AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Table pagination">
            <ul class="pagination justify-content-end">
                <!-- Dynamic pagination -->
            </ul>
        </nav>
    </div>
</div>
```

#### User Detail Modal
```html
<div class="modal fade" id="userDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <!-- Profile Photo -->
                        <img src="" class="rounded img-fluid mb-3" id="user-photo">
                        <h6 id="user-name"></h6>
                        <p class="text-muted" id="user-email"></p>
                        <span class="badge bg-primary" id="user-role"></span>
                    </div>
                    <div class="col-md-8">
                        <!-- Stats Cards -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="card bg-label-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti tabler-coin"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" id="user-points">0</h6>
                                                <small>Points Balance</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-label-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class="ti tabler-recycle"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" id="user-transactions">0</h6>
                                                <small>Transactions</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Points History Chart -->
                        <div class="card">
                            <div class="card-body">
                                <h6>Points History</h6>
                                <div id="points-history-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 📦 MODULE 2: RVM MACHINES MANAGEMENT

### Features
1. **Machine List**
   - Status indicators (online, offline, maintenance)
   - Capacity visualization (progress bars)
   - Location on map  
   - Real-time status updates (WebSocket/Polling)

2. **Machine Detail View**
   - Telemetry data (current weight, capacity %)
   - Transaction statistics
   - Maintenance history
   - Edge device linkage
   - Location map (Google Maps/Leaflet)

3. **Machine Operations**
   - Add new machine
   - Edit machine info
   - Set maintenance mode
   - View logs
   - Remote commands (future)

### Data Sources
```
GET    /api/v1/rvm-machines              ← List machines
GET    /api/v1/rvm-machines/{id}         ← Machine detail
POST   /api/v1/rvm-machines              ← Create machine
PUT    /api/v1/rvm-machines/{id}         ← Update machine
DELETE /api/v1/rvm-machines/{id}         ← Delete machine
GET    /api/v1/rvm-machines/{id}/stats   ← Machine statistics
GET    /api/v1/rvm-machines/{id}/telemetry ← Telemetry data
```

### UI Components

#### Machine Grid View
```html
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card" data-machine-id="1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">RVM Grand Indonesia</h6>
                    <span class="badge bg-success">Online</span>
                </div>
                <p class="text-muted small mb-2">Jakarta Pusat</p>
                
                <!-- Capacity Bar -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Capacity</span>
                        <span>25%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 25%"></div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="row g-2 text-center small">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">45</div>
                            <div class="text-muted">Today</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">1,234</div>
                            <div class="text-muted">Total</div>
                        </div>
                    </div>
                </div>
                
                <button class="btn btn-sm btn-outline-primary w-100 mt-2" 
                        data-bs-toggle="modal" 
                        data-bs-target="#machineDetailModal">
                    View Details
                </button>
            </div>
        </div>
    </div>
    <!-- Repeat for other machines -->
</div>
```

#### Machine Detail with Map
```html
<div class="modal fade" id="machineDetailModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="machine-name">RVM Machine Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left: Info -->
                    <div class="col-md-8">
                        <!-- Status Cards -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="avatar mx-auto mb-2">
                                            <span class="avatar-initial rounded bg-success">
                                                <i class="ti tabler-check"></i>
                                            </span>
                                        </div>
                                        <h6 class="mb-0">Online</h6>
                                        <small class="text-muted">Status</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5 class="mb-1" id="machine-capacity">25%</h5>
                                        <small class="text-muted">Capacity</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5 class="mb-1" id="machine-today-count">45</h5>
                                        <small class="text-muted">Today</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5 class="mb-1" id="machine-total-count">1,234</h5>
                                        <small class="text-muted">All Time</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Telemetry Chart -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Capacity Trend (7 Days)</h6>
                            </div>
                            <div class="card-body">
                                <div id="capacity-trend-chart"></div>
                            </div>
                        </div>
                        
                        <!-- Transaction Stats -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Transaction Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div id="transaction-stats-chart"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right: Map & Info -->
                    <div class="col-md-4">
                        <!-- Location Map -->
                        <div class="card mb-3">
                            <div class="card-body p-0">
                                <div id="machine-map" style="height: 300px;"></div>
                            </div>
                        </div>
                        
                        <!-- Machine Info -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5">Serial Number</dt>
                                    <dd class="col-sm-7" id="machine-serial">RVM-GI-001</dd>
                                    
                                    <dt class="col-sm-5">Location</dt>
                                    <dd class="col-sm-7" id="machine-location">Jakarta Pusat</dd>
                                    
                                    <dt class="col-sm-5">Last Ping</dt>
                                    <dd class="col-sm-7" id="machine-last-ping">2 min ago</dd>
                                    
                                    <dt class="col-sm-5">Edge Device</dt>
                                    <dd class="col-sm-7">
                                        <a href="#" id="machine-edge-device">JETSON-001</a>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 📦 MODULE 3: EDGE DEVICES MANAGEMENT

### Features
1. **Device List**
   - Device status (online, offline, error)
   - Current AI model version
   - Heartbeat monitoring
   - Tailscale IP display

2. **Device Detail & Monitoring**
   - Hardware info (CPU, GPU, Memory)
   - Real-time telemetry (temperature, usage)
   - AI model sync status
   - Image upload logs
   - Location tracking

3. **Device Operations**
   - Register new device
   - Update device info
   - Trigger manual model sync
   - View device logs
   - Remote diagnostics

### Data Sources
```
GET    /api/v1/edge/devices              ← List devices
GET    /api/v1/edge/devices/{id}         ← Device detail
POST   /api/v1/edge/register             ← Register device
PUT    /api/v1/edge/devices/{id}         ← Update device
DELETE /api/v1/edge/devices/{id}         ← Delete device
GET    /api/v1/edge/devices/{id}/telemetry ← Device telemetry
POST   /api/v1/edge/devices/{id}/sync    ← Trigger model sync
```

### UI Components

#### Device Cards with Status
```html
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">JETSON-GI-001</h6>
                    <span class="badge bg-success">
                        <i class="ti tabler-wifi me-1"></i>Online
                    </span>
                </div>
                
                <p class="text-muted small mb-2">
                    <i class="ti tabler-map-pin me-1"></i>
                    RVM Grand Indonesia
                </p>
                
                <!-- Hardware Stats -->
                <div class="mb-2">
                    <div class="row g-2 small">
                        <div class="col-4">
                            <div class="text-center p-2 border rounded">
                                <i class="ti tabler-cpu text-primary"></i>
                                <div class="fw-semibold">45%</div>
                                <div class="text-muted">CPU</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 border rounded">
                                <i class="ti tabler-device-desktop text-info"></i>
                                <div class="fw-semibold">62%</div>
                                <div class="text-muted">GPU</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 border rounded">
                                <i class="ti tabler-temperature text-warning"></i>
                                <div class="fw-semibold">65°C</div>
                                <div class="text-muted">Temp</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- AI Model Info -->
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-label-primary rounded">
                    <small>AI Model:</small>
                    <span class="badge bg-primary">v3.2.1</span>
                </div>
                
                <!-- Last Heartbeat -->
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Last Heartbeat:</span>
                    <span>30s ago</span>
                </div>
                
                <div class="btn-group w-100" role="group">
                    <button class="btn btn-sm btn-outline-primary" data-device-id="1">
                        <i class="ti tabler-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" data-device-id="1">
                        <i class="ti tabler-refresh"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" data-device-id="1">
                        <i class="ti tabler-settings"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### Real-time Monitoring Dashboard
```html
<div class="card">
    <div class="card-header">
        <h5>Edge Device Monitoring - JETSON-GI-001</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- CPU/GPU Usage Chart -->
            <div class="col-md-6 mb-3">
                <div class="card bg-label-primary">
                    <div class="card-header">
                        <h6 class="mb-0">CPU & GPU Usage</h6>
                    </div>
                    <div class="card-body">
                        <div id="cpu-gpu-chart"></div>
                    </div>
                </div>
            </div>
            
            <!-- Temperature Chart -->
            <div class="col-md-6 mb-3">
                <div class="card bg-label-warning">
                    <div class="card-header">
                        <h6 class="mb-0">Temperature</h6>
                    </div>
                    <div class="card-body">
                        <div id="temperature-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Recent Activity</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- Dynamic activity log -->
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 📦 MODULE 4: CV SERVERS MANAGEMENT

### Features
1. **Server List**
   - Server status
   - Active training jobs count
   - Model repository
   - GPU utilization

2. **Training Job Monitor**
   - Job status (queued, training, completed, failed)
   - Progress indicators
   - Training metrics (loss, accuracy)
   - Dataset information

3. **Model Repository**
   - Available models list
   - Version history
   - Deployment status
   - Download statistics

### Data Sources
```
GET    /api/v1/cv/servers                ← List CV servers
GET    /api/v1/cv/training-jobs          ← Training jobs list
GET    /api/v1/cv/models                 ← Model repository
POST   /api/v1/cv/train                  ← Start training
DELETE /api/v1/cv/training-jobs/{id}     ← Cancel training
```

### UI Components

#### Training Jobs Monitor
```html
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Training Jobs</h5>
        <button class="btn btn-primary btn-sm">
            <i class="ti tabler-plus me-1"></i>New Training
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Job ID</th>
                        <th>Model</th>
                        <th>Dataset</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Metrics</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#TRN-001</td>
                        <td>YOLO11-v3.2</td>
                        <td>PET-Bottles-2026</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     style="width: 65%">65%</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning">
                                <i class="ti tabler-loader"></i>Training
                            </span>
                        </td>
                        <td>
                            <small>
                                Loss: 0.045<br>
                                mAP: 0.92
                            </small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger">
                                <i class="ti tabler-x"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
```

---

## 🎨 UI/UX DESIGN SPECIFICATIONS

### Design System

#### Color Palette
```css
:root {
    /* Primary Colors */
    --primary: #696cff;
    --primary-dark: #5f61e6;
    --primary-light: #7d7ffc;
    
    /* Semantic Colors */
    --success: #71dd37;
    --warning: #ffab00;
    --danger: #ff3e1d;
    --info: #03c3ec;
    
    /* Neutral Colors */
    --gray-100: #f5f5f9;
    --gray-200: #ebeef0;
    --gray-300: #dbdade;
    --gray-900: #566a7f;
    
    /* Status Colors */
    --status-online: #71dd37;
    --status-offline: #8592a3;
    --status-maintenance: #ffab00;
    --status-error: #ff3e1d;
}
```

#### Typography
```css
/* Headers */
h1 { font-size: 2rem; font-weight: 600; }
h2 { font-size: 1.75rem; font-weight: 600; }
h3 { font-size: 1.5rem; font-weight: 600; }
h4 { font-size: 1.25rem; font-weight: 600; }
h5 { font-size: 1.125rem; font-weight: 600; }
h6 { font-size: 1rem; font-weight: 600; }

/* Body */
body { font-family: 'Public Sans', sans-serif; font-size: 0.9375rem; }
small { font-size: 0.8125rem; }
```

#### Spacing
```css
/* Margin/Padding Scale */
.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-4 { margin-bottom: 1.5rem; }
.mb-5 { margin-bottom: 3rem; }
```

### Responsive Breakpoints
```css
/* Mobile First */
@media (min-width: 576px) { /* Small devices */ }
@media (min-width: 768px) { /* Tablets */ }
@media (min-width: 992px) { /* Desktops */ }
@media (min-width: 1200px) { /* Large desktops */ }
@media (min-width: 1400px) { /* Extra large */ }
```

### Micro-interactions

#### Button Hover Effects
```css
.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn:active {
    transform: translateY(0);
}
```

#### Card Hover
```css
.card-hoverable {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card-hoverable:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
```

#### Loading States
```css
@keyframes skeleton-loading {
    0% { background-position: -200px 0; }
    100% { background-position: calc(200px + 100%) 0; }
}

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 0px, #f8f8f8 40px, #f0f0f0 80px);
    background-size: 200px 100%;
    animation: skeleton-loading 1.2s ease-in-out infinite;
}
```

---

## 📱 RESPONSIVE DESIGN

### Mobile Layout (< 768px)

#### Stacked Cards
```html
<!-- Mobile: Full-width stacked -->
<div class="col-12 mb-3">
    <div class="card">
        <!-- Card content -->
    </div>
</div>
```

#### Collapsible Sidebar
```javascript
// Auto-collapse sidebar on mobile
if (window.innerWidth < 992) {
    document.body.classList.add('layout-menu-collapsed');
}
```

### Tablet Layout (768px - 992px)

#### 2-Column Grid
```html
<div class="row">
    <div class="col-md-6 mb-3">
        <!-- Card 1 -->
    </div>
    <div class="col-md-6 mb-3">
        <!-- Card 2 -->
    </div>
</div>
```

### Desktop Layout (> 992px)

#### Multi-column Grid
```html
<div class="row">
    <div class="col-lg-3 mb-3"><!-- Card 1 --></div>
    <div class="col-lg-3 mb-3"><!-- Card 2 --></div>
    <div class="col-lg-3 mb-3"><!-- Card 3 --></div>
    <div class="col-lg-3 mb-3"><!-- Card 4 --></div>
</div>
```

---

## 🔧 TECHNICAL IMPLEMENTATION

### Backend Routes (web.php)

```php
// Dashboard Management Routes
Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // SPA Content Endpoints
    Route::get('/users/content', [UserController::class, 'indexContent']);
    Route::get('/machines/content', [MachineController::class, 'indexContent']);
    Route::get('/devices/content', [DeviceController::class, 'indexContent']);
    Route::get('/cv-servers/content', [CVServerController::class, 'indexContent']);
    
    // Full Page Routes (for direct access)
    Route::get('/users', [UserController::class, 'index'])->name('dashboard.users');
    Route::get('/machines', [MachineController::class, 'index'])->name('dashboard.machines');
    Route::get('/devices', [DeviceController::class, 'index'])->name('dashboard.devices');
    Route::get('/cv-servers', [CVServerController::class, 'index'])->name('dashboard.cv-servers');
});
```

### Example Controller

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Full page load
        return view('dashboard.users.index');
    }
    
    public function indexContent()
    {
        // SPA content only (no layout)
        return view('dashboard.users.index-content');
    }
    
    public function getUsers(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $role = $request->get('role');
        
        $query = User::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($role) {
            $query->where('role', $role);
        }
        
        $users = $query->latest()->paginate($perPage);
        
        return response()->json($users);
    }
}
```

### JavaScript Data Fetching

```javascript
// /public/js/modules/users.js

class UserManager {
    constructor() {
        this.currentPage = 1;
        this.perPage = 10;
        this.setupEventListeners();
        this.loadUsers();
    }
    
    setupEventListeners() {
        // Search
        document.getElementById('user-search').addEventListener('input', 
            this.debounce(() => this.loadUsers(), 500)
        );
        
        // Filter
        document.getElementById('role-filter').addEventListener('change', 
            () => this.loadUsers()
        );
        
        // Pagination
        document.addEventListener('click', (e) => {
            if (e.target.closest('.pagination-link')) {
                e.preventDefault();
                this.currentPage = parseInt(e.target.dataset.page);
                this.loadUsers();
            }
        });
    }
    
    async loadUsers() {
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: document.getElementById('user-search').value,
                role: document.getElementById('role-filter').value
            });
            
            const response = await fetch(`/api/v1/admin/users?${params}`);
            const data = await response.json();
            
            this.renderUsers(data.data);
            this.renderPagination(data.meta);
            
        } catch (error) {
            console.error('Failed to load users:', error);
            this.showError('Failed to load users');
        }
    }
    
    renderUsers(users) {
        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = users.map(user => `
            <tr>
                <td><input type="checkbox" class="form-check-input" value="${user.id}"></td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${user.photo_url || '/vendor/assets/img/avatars/default.png'}" 
                             class="rounded-circle me-2" width="32" height="32">
                        <span class="fw-semibold">${user.name}</span>
                    </div>
                </td>
                <td>${user.email}</td>
                <td><span class="badge bg-label-primary">${user.role}</span></td>
                <td>${user.points_balance}</td>
                <td>
                    <span class="badge bg-${user.status === 'active' ? 'success' : 'secondary'}">
                        ${user.status}
                    </span>
                </td>
                <td>${this.formatDate(user.created_at)}</td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                            <i class="ti tabler-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="userManager.viewUser(${user.id})">
                                <i class="ti tabler-eye me-2"></i>View
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="userManager.editUser(${user.id})">
                                <i class="ti tabler-edit me-2"></i>Edit
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="userManager.deleteUser(${user.id})">
                                <i class="ti tabler-trash me-2"></i>Delete
                            </a></li>
                        </ul>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    renderPagination(meta) {
        const pagination = document.querySelector('.pagination');
        // Render pagination links
    }
    
    async viewUser(userId) {
        // Load user details and show modal
    }
    
    async editUser(userId) {
        // Load user data and show edit modal
    }
    
    async deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            // Delete user via API
        }
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID');
    }
    
    showError(message) {
        // Show toast/alert
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.userManager = new UserManager();
});
```

---

## 📊 CHART CONFIGURATIONS

### ApexCharts Setup

#### Points History Chart
```javascript
const pointsHistoryOptions = {
    series: [{
        name: 'Points Earned',
        data: [30, 40, 35, 50, 49, 60, 70]
    }, {
        name: 'Points Redeemed',
        data: [10, 15, 12, 20, 18, 25, 30]
    }],
    chart: {
        type: 'area',
        height: 300,
        toolbar: { show: false }
    },
    dataLabels: { enabled: false },
    stroke: {
        curve: 'smooth',
        width: 2
    },
    colors: ['#696cff', '#ff3e1d'],
    fill: {
        type: 'gradient',
        gradient: {
            opacityFrom: 0.6,
            opacityTo: 0.1,
        }
    },
    xaxis: {
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    },
    tooltip: {
        shared: true,
        intersect: false
    }
};

const pointsChart = new ApexCharts(
    document.querySelector("#points-history-chart"), 
    pointsHistoryOptions
);
pointsChart.render();
```

#### Real-time CPU/GPU Chart
```javascript
const cpuGpuOptions = {
    series: [{
        name: 'CPU',
        data: []
    }, {
        name: 'GPU',
        data: []
    }],
    chart: {
        type: 'line',
        height: 250,
        animations: {
            enabled: true,
            easing: 'linear',
            dynamicAnimation: {
                speed: 1000
            }
        },
        toolbar: { show: false }
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    colors: ['#696cff', '#03c3ec'],
    xaxis: {
        type: 'datetime',
        range: 60000 // Last 60 seconds
    },
    yaxis: {
        max: 100,
        title: { text: 'Usage (%)' }
    }
};

const cpuGpuChart = new ApexCharts(
    document.querySelector("#cpu-gpu-chart"),
    cpuGpuOptions
);
cpuGpuChart.render();

// Update every second
setInterval(() => {
    const timestamp = new Date().getTime();
    cpuGpuChart.appendData([{
        data: [[timestamp, Math.floor(Math.random() * 100)]]
    }, {
        data: [[timestamp, Math.floor(Math.random() * 100)]]
    }]);
}, 1000);
```

---

## 🧪 TESTING STRATEGY

### Unit Testing (Jest + Testing Library)

```javascript
// tests/users.test.js

import { UserManager } from '../public/js/modules/users';

describe('UserManager', () => {
    let userManager;
    
    beforeEach(() => {
        document.body.innerHTML = `
            <input id="user-search" />
            <select id="role-filter"></select>
            <tbody id="users-table-body"></tbody>
        `;
        userManager = new UserManager();
    });
    
    test('should load users on init', async () => {
        const spy = jest.spyOn(userManager, 'loadUsers');
        await userManager.loadUsers();
        expect(spy).toHaveBeenCalled();
    });
    
    test('should filter users by role', async () => {
        // Test implementation
    });
    
    test('should handle pagination', async () => {
        // Test implementation
    });
});
```

### Integration Testing (Cypress)

```javascript
// cypress/e2e/dashboard/users.cy.js

describe('User Management', () => {
    beforeEach(() => {
        cy.login('admin@myrvm.com', 'password123');
        cy.visit('/dashboard/users');
    });
    
    it('should display user list', () => {
        cy.get('#users-table-body tr').should('have.length.greaterThan', 0);
    });
    
    it('should search users', () => {
        cy.get('#user-search').type('john');
        cy.wait(600); // Debounce
        cy.get('#users-table-body tr').should('contain', 'john');
    });
    
    it('should filter by role', () => {
        cy.get('#role-filter').select('admin');
        cy.get('#users-table-body .badge').should('contain', 'admin');
    });
    
    it('should open user detail modal', () => {
        cy.get('#users-table-body tr').first().find('button').click();
        cy.get('.dropdown-menu').find('a').contains('View').click();
        cy.get('#userDetailModal').should('be.visible');
    });
});
```

### Accessibility Testing

```javascript
// Using axe-core
describe('Accessibility', () => {
    it('should have no violations', () => {
        cy.visit('/dashboard/users');
        cy.injectAxe();
        cy.checkA11y();
    });
    
    it('should be keyboard navigable', () => {
        cy.visit('/dashboard/users');
        cy.get('body').tab(); // Tab through elements
        cy.focused().should('have.attr', 'href');
    });
});
```

### Performance Testing

```javascript
// Lighthouse CI configuration
module.exports = {
    ci: {
        collect: {
            url: [
                'http://localhost:8000/dashboard',
                'http://localhost:8000/dashboard/users',
                'http://localhost:8000/dashboard/machines'
            ],
            numberOfRuns: 3
        },
        assert: {
            assertions: {
                'categories:performance': ['warn', { minScore: 0.9 }],
                'categories:accessibility': ['error', { minScore: 0.9 }],
                'categories:best-practices': ['warn', { minScore: 0.9 }],
                'categories:seo': ['warn', { minScore: 0.9 }]
            }
        }
    }
};
```

---

## 📅 IMPLEMENTATION TIMELINE

### Phase 1: Foundation (Week 1)
**DONE by**: 17 Januari 2026

- [x] Setup SPA navigation framework
- [x] Create base layout enhancements
- [x] Implement loading states & transitions
- [x] Setup API endpoints structure
- [x] Create reusable UI components

**Deliverables**:
- SPA navigation working
- Skeleton screens implemented
- API endpoint scaffolding complete

---

### Phase 2: User & Tenants Module (Week 1-2)
**DONE by**: 24 Januari 2026

- [x] User list table with search/filter
- [x] User detail modal
- [x] Create/Edit user forms
- [x] Points history chart
- [x] Transaction history integration
- [x] Role management
- [x] Testing & bug fixes

**Deliverables**:
- Fully functional User Management module
- Unit tests (>80% coverage)
- Integration tests
- Documentation

---

### Phase 3: RVM Machines Module (Week 2-3)
**DONE by**: 31 Januari 2026

- [x] Machine grid/list view
- [x] Machine detail modal with charts
- [x] Location map integration
- [x] Telemetry visualization
- [x] Status monitoring
- [x] Maintenance mode toggle
- [x] Testing & optimization

**Deliverables**:
- RVM Machines management complete
- Real-time status updates
- Map integration working
- Performance optimized

---

### Phase 4: Edge Devices Module (Week 3-4)
**DONE by**: 7 Februari 2026

- [x] Device list with status cards
- [x] Device registration flow
- [x] Real-time monitoring dashboard
- [x] Telemetry charts (CPU, GPU, temp)
- [x] Model sync status
- [x] Device diagnostics
- [x] Testing & validation

**Deliverables**:
- Edge Devices module complete
- Real-time monitoring functional
- Model sync working
- Device management tested

---

### Phase 5: CV Servers Module (Week 4-5)
**DONE by**: 14 Februari 2026

- [x] Server list & status
- [x] Training jobs monitor
- [x] Progress tracking
- [x] Model repository
- [x] Training metrics display
- [x] Job cancellation
- [x] Testing & refinement

**Deliverables**:
- CV Servers module complete
- Training monitor working
- Model repository functional
- All tests passing

---

### Phase 6: Polish & Production Ready (Week 5-6)
**DONE by**: 21 Februari 2026

- [x] Cross-browser testing
- [x] Mobile responsiveness verification
- [x] Accessibility audit
- [x] Performance optimization
- [x] Security review
- [x] Documentation finalization
- [x] User acceptance testing (UAT)
- [x] Production deployment

**Deliverables**:
- Production-ready dashboard
- Complete documentation
- User manual
- Admin guide
- API documentation updated

---

## 🔒 SECURITY CONSIDERATIONS

### Authentication & Authorization

```php
// Middleware for role-based access
Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    // Admin-only routes
});

Route::middleware(['auth', 'role:admin,super_admin,operator'])->group(function () {
    // Operator-level routes
});
```

### CSRF Protection

```javascript
// Include CSRF token in AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(data)
});
```

### XSS Prevention

```php
// Always escape output in Blade
{{ $user->name }} // Auto-escaped
{!! $trustedHtml !!} // Unescaped (use with caution)
```

```javascript
// Sanitize user input before rendering
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}
```

### SQL Injection Prevention

```php
// Always use Eloquent or query builder with parameter binding
User::where('email', $request->email)->first(); // Safe
// NEVER: DB::raw("SELECT * FROM users WHERE email = '{$email}'"); // Unsafe!
```

---

## 📖 DOCUMENTATION DELIVERABLES

### 1. Technical Documentation

#### API Endpoints Documentation
- Complete endpoint list
- Request/response examples
- Error codes
- Rate limiting

#### Component Documentation
- Reusable component catalog
- Props/parameters
- Usage examples
- Accessibility notes

### 2. User Documentation

#### Admin User Manual
- How to manage users
- How to monitor machines
- How to view reports
- Troubleshooting guide

#### Developer Guide
- Setup instructions
- Architecture overview
- Code structure
- Deployment guide

### 3. Visual Documentation

#### Design System
- Color palette
- Typography scale
- Component library
- Icon set
- Spacing system

#### UI Mockups
- Mobile views (320px, 375px, 768px)
- Tablet views (768px, 1024px)
- Desktop views (1280px, 1920px)
- Dark mode variants

---

## 🎯 SUCCESS CRITERIA

### Functional Requirements
- [x] All 4 modules operational
- [x] SPA navigation working smoothly
- [x] Real-time data updates functional
- [x] CRUD operations complete
- [x] Search/filter working
- [x] Responsive on all devices

### Performance Requirements
- [x] Lighthouse Performance Score >90
- [x] First Contentful Paint <1.5s
- [x] Time to Interactive <3s
- [x] Largest Contentful Paint <2.5s
- [x] Cumulative Layout Shift <0.1

### Accessibility Requirements
- [x] WCAG 2.1 AA compliant
- [x] Keyboard navigable
- [x] Screen reader compatible
- [x] Color contrast ratios >4.5:1
- [x] Focus indicators visible

### Browser Compatibility
- [x] Chrome (last 2 versions)
- [x] Firefox (last 2 versions)
- [x] Safari (last 2 versions)
- [x] Edge (last 2 versions)
- [x] Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🔄 ROLLBACK PLAN

### Pre-deployment Checklist
1. Backup database
2. Tag current Git version
3. Document current state
4. Test rollback procedure in staging

### Rollback Procedure

```bash
# 1. Stop web server
sudo systemctl stop nginx

# 2. Restore previous version
git checkout tags/v1.0-stable

# 3. Restore database if needed
php artisan migrate:rollback

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 5. Restart services
sudo systemctl start nginx
```

### Post-rollback Verification
- Check dashboard accessibility
- Verify API endpoints
- Test critical user flows
- Monitor error logs

---

## 📊 MONITORING & ANALYTICS

### Performance Monitoring

```javascript
// Google Analytics
gtag('event', 'page_view', {
    page_title: 'User Management',
    page_location: window.location.href,
    page_path: '/dashboard/users'
});

// Custom events
gtag('event', 'user_action', {
    event_category: 'User Management',
    event_label: 'Create User',
    value: 1
});
```

### Error Tracking

```javascript
// Sentry integration
Sentry.init({
    dsn: 'YOUR_SENTRY_DSN',
    environment: 'production',
    integrations: [
        new Sentry.BrowserTracing()
    ],
    tracesSampleRate: 1.0
});

// Capture errors
try {
    // Code
} catch (error) {
    Sentry.captureException(error);
}
```

---

## 📝 CHANGELOG

### Version 1.0 (Initial) - 10 Januari 2026
- Document created
- Architecture defined
- Implementation plan outlined
- Timeline established

---

## ✅ NEXT STEPS

### Immediate Actions (This Week)
1. **Review & Approve** this plan
2. **Setup Development Environment** 
   - Create feature branch `feature/dashboard-management-modules`
   - Setup testing framework
3. **Begin Phase 1** implementation
   - SPA navigation framework
   - Base components

### Coordination Required
- **Design Team**: Final UI mockup approval
- **Backend Team**: API endpoint coordination
- **QA Team**: Test plan alignment
- **DevOps Team**: Staging environment setup

---

## 📞 STAKEHOLDER COMMUNICATION

### Weekly Progress Reports
- **Every Friday**: Progress update email
- **Status Dashboard**: Real-time progress tracking
- **Demo Sessions**: Bi-weekly feature demonstrations

### Issue Escalation
- **Blocker Issues**: Immediate notification
- **Risk Concerns**: Report within 24h
- **Scope Changes**: Formal change request process

---

**Document Prepared By**: Antigravity AI  
**Review Required By**: Project Manager, Tech Lead, UX Designer  
**Approval Required By**: Product Owner  
**Next Review Date**: 17 Januari 2026

---

**End of Document**
