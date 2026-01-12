/**
 * Edge Devices Management Module
 * Handles device monitoring, telemetry, real-time data
 */

class DeviceManagement {
    constructor() {
        this.devices = [];
        this.telemetryInterval = null;
        this.init();
    }

    init() {
        document.addEventListener('pageLoaded', (e) => {
            if (e.detail.page === 'devices') {
                this.setupEventListeners();
                this.loadDevices();
                this.startAutoRefresh();
            }
        });

        if (window.location.pathname.includes('/devices')) {
            this.setupEventListeners();
            this.loadDevices();
            this.startAutoRefresh();
        }
    }

    setupEventListeners() {
        const statusFilter = document.getElementById('device-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.loadDevices());
        }
    }

    async loadDevices() {
        try {
            const status = document.getElementById('device-status-filter')?.value || '';
            const params = new URLSearchParams({ status });

            const response = await fetch(`/api/v1/edge/devices?${params}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Failed to load devices');

            const data = await response.json();
            this.devices = data.data || data;

            this.renderDevices();
            this.updateStats();

        } catch (error) {
            console.error('Error loading devices:', error);
            this.showError('Failed to load devices');
        }
    }

    renderDevices() {
        const grid = document.getElementById('devices-grid');
        if (!grid) return;

        if (this.devices.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <i class="ti tabler-cpu empty-state-icon"></i>
                        <div class="empty-state-title">No devices found</div>
                    </div>
                </div>
            `;
            return;
        }

        grid.innerHTML = this.devices.map(device => `
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0">${this.escapeHtml(device.device_serial)}</h6>
                            <span class="badge ${device.is_online ? 'badge-status-online' : 'badge-status-offline'}">
                                <i class="ti ${device.is_online ? 'tabler-wifi' : 'tabler-wifi-off'} me-1"></i>
                                ${device.is_online ? 'Online' : 'Offline'}
                            </span>
                        </div>
                        
                        <p class="text-muted small mb-2">
                            <i class="ti tabler-map-pin me-1"></i>
                            ${device.rvm_machine?.name || 'No RVM assigned'}
                        </p>
                        
                        <!-- Hardware Stats -->
                        <div class="mb-2">
                            <div class="row g-2 small">
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded">
                                        <i class="ti tabler-cpu text-primary"></i>
                                        <div class="fw-semibold">${device.cpu_usage || 0}%</div>
                                        <div class="text-muted">CPU</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded">
                                        <i class="ti tabler-device-desktop text-info"></i>
                                        <div class="fw-semibold">${device.gpu_usage || 0}%</div>
                                        <div class="text-muted">GPU</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded">
                                        <i class="ti tabler-temperature text-warning"></i>
                                        <div class="fw-semibold">${device.temperature || 0}°C</div>
                                        <div class="text-muted">Temp</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- AI Model Info -->
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-label-primary rounded small">
                            <span>AI Model:</span>
                            <span class="badge bg-primary">${device.current_model_version || 'N/A'}</span>
                        </div>
                        
                        <!-- Last Heartbeat -->
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Last Heartbeat:</span>
                            <span>${this.getLastSeen(device.last_seen)}</span>
                        </div>
                        
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="deviceManagement.monitorDevice(${device.id})">
                                <i class="ti tabler-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="deviceManagement.syncModel(${device.id})">
                                <i class="ti tabler-refresh"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info">
                                <i class="ti tabler-settings"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateStats() {
        const online = this.devices.filter(d => d.is_online).length;
        const offline = this.devices.length - online;
        const avgCpu = this.calculateAverage(this.devices, 'cpu_usage');
        const avgGpu = this.calculateAverage(this.devices, 'gpu_usage');
        const avgTemp = this.calculateAverage(this.devices, 'temperature');

        document.getElementById('devices-online').textContent = online;
        document.getElementById('devices-offline').textContent = offline;
        document.getElementById('avg-cpu').textContent = `${avgCpu}%`;
        document.getElementById('avg-gpu').textContent = `${avgGpu}%`;
        document.getElementById('avg-temp').textContent = `${avgTemp}°C`;
        document.getElementById('total-devices').textContent = this.devices.length;
    }

    calculateAverage(devices, field) {
        const validDevices = devices.filter(d => d[field] != null);
        if (validDevices.length === 0) return 0;
        const sum = validDevices.reduce((acc, d) => acc + (d[field] || 0), 0);
        return Math.round(sum / validDevices.length);
    }

    async monitorDevice(deviceId) {
        const modal = new bootstrap.Modal(document.getElementById('deviceMonitorModal'));
        const content = document.getElementById('device-monitor-content');

        modal.show();
        content.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';

        try {
            const response = await fetch(`/api/v1/edge/devices/${deviceId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Failed to load device details');

            const data = await response.json();
            const device = data.data || data;

            document.getElementById('device-serial').textContent = `Device: ${device.device_serial}`;

            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-label-primary">
                            <div class="card-header"><h6 class="mb-0">System Usage</h6></div>
                            <div class="card-body">
                                <div id="system-usage-chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-label-warning">
                            <div class="card-header"><h6 class="mb-0">Temperature</h6></div>
                            <div class="card-body">
                                <div id="temperature-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">Device Information</h6></div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Serial Number</dt>
                            <dd class="col-sm-9">${device.device_serial}</dd>
                            <dt class="col-sm-3">RVM Machine</dt>
                            <dd class="col-sm-9">${device.rvm_machine?.name || 'Not assigned'}</dd>
                            <dt class="col-sm-3">Tailscale IP</dt>
                            <dd class="col-sm-9">${device.tailscale_ip || 'N/A'}</dd>
                            <dt class="col-sm-3">AI Model Version</dt>
                            <dd class="col-sm-9">${device.current_model_version || 'N/A'}</dd>
                            <dt class="col-sm-3">Last Seen</dt>
                            <dd class="col-sm-9">${this.getLastSeen(device.last_seen)}</dd>
                        </dl>
                    </div>
                </div>
            `;

            // Render charts (mock data for now)
            this.renderSystemChart();
            this.renderTempChart();

        } catch (error) {
            console.error('Error loading device details:', error);
            content.innerHTML = '<div class="alert alert-danger">Failed to load device details</div>';
        }
    }

    renderSystemChart() {
        if (typeof ApexCharts === 'undefined') return;

        const options = {
            series: [45, 60],
            chart: { type: 'donut', height: 200 },
            labels: ['CPU', 'GPU'],
            colors: ['#696cff', '#03c3ec'],
            legend: { position: 'bottom' }
        };

        const chart = new ApexCharts(document.querySelector("#system-usage-chart"), options);
        chart.render();
    }

    renderTempChart() {
        if (typeof ApexCharts === 'undefined') return;

        const options = {
            series: [{
                name: 'Temperature',
                data: [55, 57, 60, 58, 62, 65]
            }],
            chart: { type: 'line', height: 200, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#ffab00'],
            xaxis: { categories: ['10m', '8m', '6m', '4m', '2m', 'Now'] }
        };

        const chart = new ApexCharts(document.querySelector("#temperature-chart"), options);
        chart.render();
    }

    async syncModel(deviceId) {
        if (!confirm('Trigger manual model sync for this device?')) return;

        // TODO: Implement model sync API call
        alert('Model sync triggered!');
    }

    startAutoRefresh() {
        // Refresh every 30 seconds
        this.telemetryInterval = setInterval(() => {
            this.loadDevices();
        }, 30000);
    }

    getLastSeen(lastSeen) {
        if (!lastSeen) return 'Never';
        const seconds = Math.floor((new Date() - new Date(lastSeen)) / 1000);
        if (seconds < 60) return `${seconds}s ago`;
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes}m ago`;
        const hours = Math.floor(minutes / 60);
        return `${hours}h ago`;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError(message) {
        console.error(message);
    }
}

const deviceManagement = new DeviceManagement();
