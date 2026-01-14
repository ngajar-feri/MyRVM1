/**
 * RVM Machines Management Module
 * Handles machines grid, monitoring, stats
 */

class MachineManagement {
    constructor() {
        this.machines = [];
        this.viewMode = 'grid'; // grid or list
        this.bootstrapReady = false;
        this.init();
    }

    init() {
        document.addEventListener('pageLoaded', (e) => {
            if (e.detail.page === 'machines') {
                this.waitForBootstrap().then(() => {
                    this.setupEventListeners();
                    this.loadMachines();
                });
            }
        });

        if (window.location.pathname.includes('/machines')) {
            this.waitForBootstrap().then(() => {
                this.setupEventListeners();
                this.loadMachines();
            });
        }
    }

    // Wait for Bootstrap to be fully loaded
    waitForBootstrap() {
        return new Promise((resolve) => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                this.bootstrapReady = true;
                resolve();
                return;
            }

            const checkBootstrap = setInterval(() => {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    clearInterval(checkBootstrap);
                    this.bootstrapReady = true;
                    resolve();
                }
            }, 100);

            // Timeout after 5 seconds
            setTimeout(() => {
                clearInterval(checkBootstrap);
                this.bootstrapReady = true;
                resolve();
            }, 5000);
        });
    }

    setupEventListeners() {
        // Status filter
        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.loadMachines());
        }

        // Location filter
        const locationFilter = document.getElementById('location-filter');
        if (locationFilter) {
            locationFilter.addEventListener('input', this.debounce(() => this.loadMachines(), 500));
        }

        // Toggle view
        const toggleView = document.getElementById('toggle-view');
        if (toggleView) {
            toggleView.addEventListener('click', () => this.toggleView());
        }
    }

    async loadMachines() {
        try {
            const statusFilter = document.getElementById('status-filter')?.value || '';
            const locationFilter = document.getElementById('location-filter')?.value || '';

            const params = new URLSearchParams({ status: statusFilter, location: locationFilter });

            // Use apiHelper with Bearer Token for authenticated API call
            const response = await apiHelper.get(`/api/v1/rvm-machines?${params}`);

            if (!response || !response.ok) throw new Error('Failed to load machines');

            const data = await response.json();
            this.machines = data.data || data;

            this.renderMachines();
            this.updateStats();

        } catch (error) {
            console.error('Error loading machines:', error);
            this.showError('Failed to load machines');
        }
    }

    renderMachines() {
        const grid = document.getElementById('machines-grid');
        if (!grid) return;

        if (this.machines.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <i class="ti tabler-device-desktop-analytics empty-state-icon"></i>
                        <div class="empty-state-title">No machines found</div>
                    </div>
                </div>
            `;
            return;
        }

        grid.innerHTML = this.machines.map(machine => {
            const edgeDevice = machine.edge_device;
            const edgeStatus = edgeDevice?.status || 'not_registered';
            const edgeStatusBadge = edgeDevice
                ? `<span class="badge bg-label-${edgeDevice.status === 'online' ? 'success' : 'secondary'}" data-bs-toggle="tooltip" title="Edge Device: ${edgeDevice.status}">
                    <i class="ti tabler-cpu"></i>
                   </span>`
                : `<span class="badge bg-label-warning" data-bs-toggle="tooltip" title="No Edge Device">
                    <i class="ti tabler-cpu-off"></i>
                   </span>`;

            return `
            <div class="col-md-4">
                <div class="card card-hoverable" onclick="machineManagement.viewMachine(${machine.id})">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="card-title mb-0">${this.escapeHtml(machine.name)}</h6>
                            <div class="d-flex gap-1">
                                ${edgeStatusBadge}
                                <span class="badge badge-status-${machine.status || 'offline'}">
                                    ${machine.status || 'offline'}
                                </span>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="ti tabler-map-pin me-1"></i>
                            ${this.escapeHtml(machine.location || 'No location')}
                        </p>
                        
                        <!-- Capacity Bar -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Bin Capacity</span>
                                <span>${machine.capacity_percentage || 0}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar ${this.getCapacityColor(machine.capacity_percentage)}" 
                                     style="width: ${machine.capacity_percentage || 0}%"></div>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="row g-2 text-center small">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">${machine.today_count || 0}</div>
                                    <div class="text-muted">Today</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-semibold">${machine.total_count || 0}</div>
                                    <div class="text-muted">Total</div>
                                </div>
                            </div>
                        </div>

                        ${edgeDevice ? `
                        <div class="mt-2 pt-2 border-top small text-muted">
                            <i class="ti tabler-heart-rate-monitor me-1"></i>
                            Last heartbeat: ${this.getLastSeen(edgeDevice.last_heartbeat)}
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `}).join('');

        // Initialize tooltips
        const tooltips = grid.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }

    updateStats() {
        const online = this.machines.filter(m => m.status === 'online').length;
        const offline = this.machines.filter(m => m.status === 'offline').length;
        const maintenance = this.machines.filter(m => m.status === 'maintenance').length;
        const totalTransactions = this.machines.reduce((sum, m) => sum + (m.total_count || 0), 0);

        document.getElementById('online-count').textContent = online;
        document.getElementById('offline-count').textContent = offline;
        document.getElementById('maintenance-count').textContent = maintenance;
        document.getElementById('total-transactions').textContent = totalTransactions;
    }

    async viewMachine(machineId) {
        await this.waitForBootstrap();

        const modalEl = document.getElementById('machineDetailModal');
        if (!modalEl) {
            console.error('Machine detail modal not found');
            return;
        }

        const modal = new bootstrap.Modal(modalEl);
        const content = document.getElementById('machine-detail-content');

        modal.show();
        content.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';

        try {
            const response = await apiHelper.get(`/api/v1/rvm-machines/${machineId}`);

            if (!response || !response.ok) throw new Error('Failed to load machine details');

            const data = await response.json();
            const machine = data.data || data;
            const edgeDevice = machine.edge_device;
            const telemetry = edgeDevice?.telemetry || [];

            document.getElementById('machine-name').textContent = machine.name;

            content.innerHTML = `
                <div class="row">
                    <!-- Left Column: Stats -->
                    <div class="col-md-8">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <span class="badge badge-status-${machine.status}">${machine.status}</span>
                                        <div class="small text-muted mt-1">Machine Status</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="mb-0">${machine.capacity_percentage || 0}%</h5>
                                        <div class="small text-muted">Bin Capacity</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="mb-0">${machine.today_count || 0}</h5>
                                        <div class="small text-muted">Today</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="mb-0">${machine.total_count || 0}</h5>
                                        <div class="small text-muted">All Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edge Device Section -->
                        ${edgeDevice ? `
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="ti tabler-cpu me-2"></i>Edge Device</h6>
                                <span class="badge badge-status-${edgeDevice.status || 'offline'}">${edgeDevice.status || 'offline'}</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="row mb-0 small">
                                            <dt class="col-5">Device ID:</dt>
                                            <dd class="col-7"><code>${edgeDevice.device_id || 'N/A'}</code></dd>
                                            <dt class="col-5">Type:</dt>
                                            <dd class="col-7">${edgeDevice.type || 'N/A'}</dd>
                                            <dt class="col-5">Firmware:</dt>
                                            <dd class="col-7">${edgeDevice.firmware_version || 'N/A'}</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0 small">
                                            <dt class="col-5">IP Address:</dt>
                                            <dd class="col-7"><code>${edgeDevice.ip_address || 'N/A'}</code></dd>
                                            <dt class="col-5">Updated:</dt>
                                            <dd class="col-7">${this.getLastSeen(edgeDevice.updated_at)}</dd>
                                        </dl>
                                    </div>
                                </div>
                                ${edgeDevice.health_metrics ? `
                                <hr>
                                <h6 class="small fw-semibold mb-2">Health Metrics</h6>
                                <pre class="bg-light p-2 rounded small mb-0" style="max-height: 100px; overflow: auto;">${JSON.stringify(edgeDevice.health_metrics, null, 2)}</pre>
                                ` : ''}
                            </div>
                        </div>
                        ` : `
                        <div class="alert alert-warning mb-3">
                            <i class="ti tabler-alert-circle me-1"></i>
                            No Edge Device registered for this machine.
                        </div>
                        `}

                        <!-- Telemetry Section -->
                        ${telemetry.length > 0 ? `
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti tabler-chart-line me-2"></i>Latest Telemetry (${telemetry.length})</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Timestamp</th>
                                                <th>Sensor Data</th>
                                                <th>Sync</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${telemetry.map(t => `
                                            <tr>
                                                <td class="small">${new Date(t.client_timestamp).toLocaleString()}</td>
                                                <td><code class="small">${JSON.stringify(t.sensor_data).substring(0, 50)}...</code></td>
                                                <td><span class="badge bg-${t.sync_status === 'synced' ? 'success' : 'warning'}">${t.sync_status}</span></td>
                                            </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>

                    <!-- Right Column: Info -->
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Machine Information</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0 small">
                                    <dt class="col-5">Serial:</dt>
                                    <dd class="col-7">${machine.serial_number || 'N/A'}</dd>
                                    <dt class="col-5">Location:</dt>
                                    <dd class="col-7">${machine.location || 'N/A'}</dd>
                                    <dt class="col-5">Last Ping:</dt>
                                    <dd class="col-7">${this.getLastSeen(machine.last_ping)}</dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Components Overview -->
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0">Components</h6></div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-label-primary" data-bs-toggle="tooltip" title="Jetson Orin Nano">
                                        <i class="ti tabler-cpu"></i> Edge Device
                                    </span>
                                    <span class="badge bg-label-info" data-bs-toggle="tooltip" title="CSI Camera">
                                        <i class="ti tabler-camera"></i> Camera
                                    </span>
                                    <span class="badge bg-label-secondary" data-bs-toggle="tooltip" title="LCD Touch Screen">
                                        <i class="ti tabler-device-tablet"></i> LCD
                                    </span>
                                    <span class="badge bg-label-warning" data-bs-toggle="tooltip" title="ESP32 Controller">
                                        <i class="ti tabler-circuit-board"></i> ESP32
                                    </span>
                                    <span class="badge bg-label-success" data-bs-toggle="tooltip" title="Sensors">
                                        <i class="ti tabler-radar"></i> Sensors
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Initialize tooltips
            const tooltips = content.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(el => new bootstrap.Tooltip(el));

        } catch (error) {
            console.error('Error loading machine details:', error);
            content.innerHTML = '<div class="alert alert-danger">Failed to load machine details</div>';
        }
    }

    getCapacityColor(percentage) {
        if (percentage >= 80) return 'progress-bar-danger';
        if (percentage >= 50) return 'progress-bar-warning';
        return 'progress-bar-success';
    }

    getLastSeen(lastSeen) {
        if (!lastSeen) return 'Never';
        const minutes = Math.floor((new Date() - new Date(lastSeen)) / 60000);
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes} min ago`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        return new Date(lastSeen).toLocaleDateString();
    }

    toggleView() {
        this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
        this.renderMachines();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    showError(message) {
        console.error(message);
    }
}

const machineManagement = new MachineManagement();
