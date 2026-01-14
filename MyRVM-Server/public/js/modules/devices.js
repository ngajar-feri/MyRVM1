/**
 * Edge Devices Management Module
 * Handles device registration, monitoring, telemetry, real-time data
 */

class DeviceManagement {
    constructor() {
        this.devices = [];
        this.telemetryInterval = null;
        this.map = null;
        this.marker = null;
        this.lastRegisteredDevice = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDevices();
        this.startAutoRefresh();

        // Initialize map AND load RVM machines when modal is shown (not before)
        const registerModal = document.getElementById('registerDeviceModal');
        if (registerModal) {
            registerModal.addEventListener('shown.bs.modal', () => {
                this.initializeMap();
                this.loadRvmMachines(); // Load when modal is visible
            });
        }

        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }

    setupEventListeners() {
        const form = document.getElementById('register-device-form');
        if (form) {
            form.addEventListener('submit', (e) => this.registerDevice(e));
        }

        const statusFilter = document.getElementById('device-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.loadDevices());
        }
    }

    initializeMap() {
        if (this.map) {
            // Already initialized, just refresh
            this.map.invalidateSize();
            return;
        }

        const mapContainer = document.getElementById('device-map');
        if (!mapContainer) {
            console.warn('Map container not found');
            return;
        }

        try {
            // Default to Jakarta, Indonesia
            const defaultLat = -6.2088;
            const defaultLng = 106.8456;

            this.map = L.map('device-map').setView([defaultLat, defaultLng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            // Click to place marker
            this.map.on('click', (e) => this.placeMarker(e.latlng));

            // Multiple invalidateSize calls to handle modal animation
            setTimeout(() => this.map.invalidateSize(), 100);
            setTimeout(() => this.map.invalidateSize(), 300);
            setTimeout(() => this.map.invalidateSize(), 500);

            console.log('Map initialized successfully');
        } catch (error) {
            console.error('Failed to initialize map:', error);
        }
    }

    placeMarker(latlng) {
        const { lat, lng } = latlng;

        // Update or create marker
        if (this.marker) {
            this.marker.setLatLng(latlng);
        } else {
            this.marker = L.marker(latlng, { draggable: true }).addTo(this.map);
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.updateCoordinates(pos.lat, pos.lng);
            });
        }

        this.updateCoordinates(lat, lng);
    }

    updateCoordinates(lat, lng) {
        document.getElementById('device-latitude').value = lat.toFixed(8);
        document.getElementById('device-longitude').value = lng.toFixed(8);

        // Reverse geocoding using Nominatim (OSM)
        this.reverseGeocode(lat, lng);
    }

    async reverseGeocode(lat, lng) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
                { headers: { 'Accept-Language': 'id' } }
            );
            const data = await response.json();
            if (data.display_name) {
                document.getElementById('device-address').value = data.display_name;
            }
        } catch (error) {
            console.warn('Reverse geocoding failed:', error);
        }
    }

    async loadDevices() {
        const statusFilter = document.getElementById('device-status-filter')?.value || '';
        const grid = document.getElementById('devices-grid');

        try {
            const params = statusFilter ? `?status=${statusFilter}` : '';
            const response = await fetch(`/api/v1/edge/devices${params}`, {
                headers: {
                    'Authorization': `Bearer ${window.authToken}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load devices');

            const result = await response.json();
            this.devices = result.data || [];
            this.renderDevices();
            this.updateStats(result.stats);
        } catch (error) {
            console.error('Error loading devices:', error);
            grid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="ti tabler-alert-circle me-2"></i>
                        Failed to load devices: ${error.message}
                    </div>
                </div>
            `;
        }
    }

    async loadRvmMachines() {
        const select = document.getElementById('rvm-machine-select');

        if (!select) {
            console.warn('RVM machine select element not found');
            return;
        }

        // Skip if already loaded (more than just the placeholder option)
        if (select.options.length > 1) {
            console.log('RVM machines already loaded');
            return;
        }

        try {
            const response = await fetch('/api/v1/rvm-machines', {
                headers: {
                    'Authorization': `Bearer ${window.authToken}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.warn('RVM machines API returned:', response.status);
                return;
            }

            const result = await response.json();

            // Handle both array and {data: array} formats
            const machines = Array.isArray(result) ? result : (result.data || []);

            console.log(`Loaded ${machines.length} RVM machines`);

            machines.forEach(machine => {
                const option = document.createElement('option');
                option.value = machine.id;
                option.textContent = `${machine.serial_number || machine.uuid || 'Unknown'} - ${machine.location_name || machine.location || 'N/A'}`;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Failed to load RVM machines:', error);
        }
    }

    renderDevices() {
        const grid = document.getElementById('devices-grid');
        if (!grid) return;

        if (this.devices.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="ti tabler-info-circle me-2"></i>
                        No Edge devices registered yet. Click "Register Device" to add one.
                    </div>
                </div>
            `;
            return;
        }

        grid.innerHTML = this.devices.map(device => this.renderDeviceCard(device)).join('');
    }

    renderDeviceCard(device) {
        const statusColors = {
            online: 'success',
            offline: 'danger',
            maintenance: 'warning',
            error: 'danger'
        };
        const statusColor = statusColors[device.status] || 'secondary';
        const healthMetrics = device.health_metrics || {};

        return `
            <div class="col-md-4">
                <div class="card h-100 device-card" onclick="deviceManagement.monitorDevice(${device.id})">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <span class="badge bg-${statusColor}">${this.escapeHtml(device.status || 'unknown')}</span>
                        <small class="text-muted">${this.getLastSeen(device.updated_at)}</small>
                    </div>
                    <div class="card-body py-3">
                        <h6 class="mb-1">${this.escapeHtml(device.location_name || device.device_id || 'Unnamed Device')}</h6>
                        <small class="text-muted d-block mb-2">
                            <i class="ti tabler-cpu me-1"></i>${this.escapeHtml(device.controller_type || device.type || 'N/A')}
                        </small>
                        <div class="d-flex justify-content-between small">
                            <span><i class="ti tabler-network me-1"></i>${this.escapeHtml(device.tailscale_ip || device.ip_address_local || 'N/A')}</span>
                        </div>
                        ${healthMetrics.cpu_usage !== undefined ? `
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small text-muted">
                                <span>CPU: ${healthMetrics.cpu_usage}%</span>
                                <span>Temp: ${healthMetrics.temperature || 'N/A'}°C</span>
                            </div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-${healthMetrics.cpu_usage > 80 ? 'danger' : 'success'}" 
                                    style="width: ${healthMetrics.cpu_usage}%"></div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    <div class="card-footer py-2">
                        <small class="text-muted">
                            <i class="ti tabler-building me-1"></i>
                            ${device.rvm_machine ? this.escapeHtml(device.rvm_machine.serial_number || device.rvm_machine.location_name) : 'Unassigned'}
                        </small>
                    </div>
                </div>
            </div>
        `;
    }

    updateStats(stats) {
        if (!stats) return;
        document.getElementById('devices-online').textContent = stats.online || 0;
        document.getElementById('devices-offline').textContent = stats.offline || 0;
        document.getElementById('avg-cpu').textContent = `${stats.avg_cpu || 0}%`;
        document.getElementById('avg-gpu').textContent = `${stats.avg_gpu || 0}%`;
        document.getElementById('avg-temp').textContent = `${stats.avg_temp || 0}°C`;
        document.getElementById('total-devices').textContent = stats.total || 0;
    }

    async registerDevice(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validate required fields
        if (!data.location_name) {
            window.showToast('Error', 'Location name is required', 'error');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Registering...';

        try {
            const response = await fetch('/api/v1/edge/register', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.authToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_serial: `EDGE-${Date.now()}`, // Auto-generate serial
                    rvm_id: data.rvm_machine_id || null,
                    tailscale_ip: null,
                    hardware_info: {
                        controller_type: data.controller_type,
                        camera_id: data.camera_id,
                        threshold_full: parseInt(data.threshold_full) || 90
                    },
                    location_name: data.location_name,
                    inventory_code: data.inventory_code,
                    description: data.description,
                    latitude: data.latitude ? parseFloat(data.latitude) : null,
                    longitude: data.longitude ? parseFloat(data.longitude) : null,
                    address: data.address,
                    status: data.status,
                    ai_model_version: data.ai_model_version
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Registration failed');
            }

            // Store for download config
            this.lastRegisteredDevice = {
                device_id: result.data.edge_device_id,
                api_key: result.data.api_key,
                location_name: data.location_name,
                config: result.data.config
            };

            // Show success modal with API key
            document.getElementById('success-device-id').value = result.data.edge_device_id;
            document.getElementById('success-api-key').value = result.data.api_key;

            // Close register modal and show success modal
            bootstrap.Modal.getInstance(document.getElementById('registerDeviceModal')).hide();
            new bootstrap.Modal(document.getElementById('successModal')).show();

            form.reset();
            if (this.marker) {
                this.map.removeLayer(this.marker);
                this.marker = null;
            }

            window.showToast('Success', 'Device registered successfully!', 'success');
        } catch (error) {
            console.error('Registration error:', error);
            window.showToast('Error', error.message || 'Failed to register device', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    monitorDevice(deviceId) {
        const device = this.devices.find(d => d.id === deviceId);
        if (!device) return;

        document.getElementById('device-serial').textContent = device.location_name || device.device_id || 'Device Monitor';

        const content = document.getElementById('device-monitor-content');
        const healthMetrics = device.health_metrics || {};

        content.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti tabler-id me-2"></i>Device Info</h6>
                            <dl class="row mb-0 small">
                                <dt class="col-5">Device ID:</dt>
                                <dd class="col-7"><code>${this.escapeHtml(device.device_id || 'N/A')}</code></dd>
                                <dt class="col-5">Controller:</dt>
                                <dd class="col-7">${this.escapeHtml(device.controller_type || device.type || 'N/A')}</dd>
                                <dt class="col-5">Status:</dt>
                                <dd class="col-7"><span class="badge bg-${device.status === 'online' ? 'success' : 'danger'}">${this.escapeHtml(device.status || 'unknown')}</span></dd>
                                <dt class="col-5">Tailscale IP:</dt>
                                <dd class="col-7"><code>${this.escapeHtml(device.tailscale_ip || 'N/A')}</code></dd>
                                <dt class="col-5">Local IP:</dt>
                                <dd class="col-7"><code>${this.escapeHtml(device.ip_address_local || 'N/A')}</code></dd>
                                <dt class="col-5">Camera:</dt>
                                <dd class="col-7">${this.escapeHtml(device.camera_id || 'N/A')}</dd>
                                <dt class="col-5">AI Model:</dt>
                                <dd class="col-7">${this.escapeHtml(device.ai_model_version || 'default')}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti tabler-activity me-2"></i>Health Metrics</h6>
                            ${Object.keys(healthMetrics).length > 0 ? `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>CPU Usage</small>
                                        <small>${healthMetrics.cpu_usage || 0}%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-${(healthMetrics.cpu_usage || 0) > 80 ? 'danger' : 'success'}" 
                                            style="width: ${healthMetrics.cpu_usage || 0}%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>GPU Usage</small>
                                        <small>${healthMetrics.gpu_usage || 0}%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: ${healthMetrics.gpu_usage || 0}%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Temperature</small>
                                        <small>${healthMetrics.temperature || 0}°C</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-${(healthMetrics.temperature || 0) > 70 ? 'danger' : 'warning'}" 
                                            style="width: ${Math.min((healthMetrics.temperature || 0), 100)}%"></div>
                                    </div>
                                </div>
                            ` : '<p class="text-muted mb-0">No health metrics available</p>'}
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ti tabler-map-pin me-2"></i>Location</h6>
                            <p class="mb-1"><strong>${this.escapeHtml(device.location_name || 'N/A')}</strong></p>
                            <p class="text-muted small mb-0">${this.escapeHtml(device.address || 'No address')}</p>
                            ${device.latitude && device.longitude ? `
                                <small class="text-muted">Coordinates: ${device.latitude}, ${device.longitude}</small>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        new bootstrap.Modal(document.getElementById('deviceMonitorModal')).show();
    }

    startAutoRefresh() {
        // Refresh every 60 seconds
        setInterval(() => this.loadDevices(), 60000);
    }

    getLastSeen(lastSeen) {
        if (!lastSeen) return 'Never';
        const diff = Date.now() - new Date(lastSeen).getTime();
        const minutes = Math.floor(diff / 60000);
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}h ago`;
        return `${Math.floor(hours / 24)}d ago`;
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global functions
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    navigator.clipboard.writeText(element.value).then(() => {
        window.showToast('Copied!', 'Value copied to clipboard', 'success');
    }).catch(err => {
        console.error('Copy failed:', err);
        element.select();
        document.execCommand('copy');
        window.showToast('Copied!', 'Value copied to clipboard', 'success');
    });
}

function downloadConfig() {
    const device = deviceManagement.lastRegisteredDevice;
    if (!device) {
        window.showToast('Error', 'No device data available', 'error');
        return;
    }

    const config = {
        rvm_edge_config: {
            device_id: device.device_id,
            api_key: device.api_key,
            location_name: device.location_name,
            server_url: window.location.origin,
            ...device.config
        },
        generated_at: new Date().toISOString(),
        warning: "Keep this file secure. API key should not be shared."
    };

    const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `rvm-edge-config-${device.device_id}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    window.showToast('Downloaded', 'Config file downloaded successfully', 'success');
}

const deviceManagement = new DeviceManagement();
