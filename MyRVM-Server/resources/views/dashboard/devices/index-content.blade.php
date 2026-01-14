<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-cpu me-2"></i>Edge Devices Management
                </h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#registerDeviceModal" data-bs-toggle="tooltip"
                    title="Register a new Edge Device (Jetson/ESP32)">
                    <i class="ti tabler-plus me-1"></i>Register Device
                </button>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <select id="device-status-filter" class="form-select" data-bs-toggle="tooltip"
                            title="Filter devices by status">
                            <option value="">All Devices</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-label-secondary w-100" onclick="window.refreshPage()"
                            data-bs-toggle="tooltip" title="Refresh device list and stats">
                            <i class="ti tabler-refresh me-1"></i>Refresh Status
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-2">
                        <div class="card stats-card stats-card-success mb-0">
                            <div class="card-body text-center">
                                <div class="avatar mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti tabler-wifi"></i>
                                    </span>
                                </div>
                                <h5 class="mb-0" id="devices-online">0</h5>
                                <small>Online</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card stats-card stats-card-danger mb-0">
                            <div class="card-body text-center">
                                <div class="avatar mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="ti tabler-wifi-off"></i>
                                    </span>
                                </div>
                                <h5 class="mb-0" id="devices-offline">0</h5>
                                <small>Offline</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card stats-card stats-card-primary mb-0">
                            <div class="card-body text-center">
                                <div class="avatar mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="ti tabler-cpu"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0" id="avg-cpu">0%</h6>
                                <small>Avg CPU</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card stats-card stats-card-info mb-0">
                            <div class="card-body text-center">
                                <div class="avatar mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="ti tabler-device-desktop"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0" id="avg-gpu">0%</h6>
                                <small>Avg GPU</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card stats-card stats-card-warning mb-0">
                            <div class="card-body text-center">
                                <div class="avatar mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="ti tabler-temperature"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0" id="avg-temp">0°C</h6>
                                <small>Avg Temp</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card stats-card stats-card-primary mb-0">
                            <div class="card-body text-center">
                                <div class="avatar mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="ti tabler-server"></i>
                                    </span>
                                </div>
                                <h5 class="mb-0" id="total-devices">0</h5>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devices Grid -->
                <div class="row g-3" id="devices-grid">
                    <!-- Loading skeleton -->
                    <div class="col-md-4">
                        <div class="skeleton skeleton-card"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="skeleton skeleton-card"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="skeleton skeleton-card"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Device Monitoring Modal -->
<div class="modal fade" id="deviceMonitorModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="device-serial">Device Monitoring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="device-monitor-content">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Device Modal (Redesigned: 3 Sections) -->
<div class="modal fade" id="registerDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ti tabler-plus me-2"></i>Register New Edge Device</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="register-device-form">
                <div class="modal-body">
                    <!-- Section 1: Identity & Status -->
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="ti tabler-id me-2"></i>Section 1: Identity & Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Location Name <span class="text-danger">*</span></label>
                                    <input type="text" name="location_name" class="form-control"
                                        placeholder="RVM Lobi Mall Grand Indonesia" required data-bs-toggle="tooltip"
                                        title="Nama unik lokasi pemasangan RVM">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Inventory Code</label>
                                    <input type="text" name="inventory_code" class="form-control"
                                        placeholder="INV-2026-001" data-bs-toggle="tooltip"
                                        title="Kode aset internal perusahaan">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Initial Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required data-bs-toggle="tooltip"
                                        title="Status awal perangkat (jangan pilih Online)">
                                        <option value="maintenance" selected>Maintenance</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <small class="text-muted">Status 'Online' dideteksi otomatis dari heartbeat</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">RVM Machine</label>
                                    <select name="rvm_machine_id" class="form-select" id="rvm-machine-select"
                                        data-bs-toggle="tooltip" title="Pilih mesin RVM yang akan dipasangi device ini">
                                        <option value="">-- Select RVM Machine --</option>
                                        <!-- Dynamic options loaded via JS -->
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description / Notes</label>
                                    <textarea name="description" class="form-control" rows="2"
                                        placeholder="Catatan khusus untuk teknisi (misal: Posisi di dekat ATM Center)"
                                        data-bs-toggle="tooltip" title="Catatan tambahan untuk teknisi"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Geolocation -->
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="ti tabler-map-pin me-2"></i>Section 2: Geolocation</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Pick Location on Map <span
                                            class="text-danger">*</span></label>
                                    <div id="device-map"
                                        style="height: 250px; border-radius: 8px; border: 1px solid #ddd;"></div>
                                    <small class="text-muted">Klik pada peta untuk menentukan lokasi. Koordinat dan
                                        alamat akan terisi otomatis.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" class="form-control" id="device-latitude"
                                        readonly placeholder="Auto from map">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" class="form-control" id="device-longitude"
                                        readonly placeholder="Auto from map">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" id="device-address"
                                        placeholder="Auto-fill from geocoding" data-bs-toggle="tooltip"
                                        title="Alamat lengkap (otomatis dari peta atau manual)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Hardware Configuration -->
                    <div class="card">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="ti tabler-cpu me-2"></i>Section 3: Hardware Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Controller Type <span class="text-danger">*</span></label>
                                    <select name="controller_type" class="form-select" required data-bs-toggle="tooltip"
                                        title="Jenis kontroler utama">
                                        <option value="NVIDIA Jetson" selected>NVIDIA Jetson</option>
                                        <option value="RaspberryPI">Raspberry PI</option>
                                        <option value="ESP32">ESP32</option>
                                        <option value="ESP8266">ESP8266</option>
                                        <option value="Arduino">Arduino</option>
                                        <option value="STM32">STM32</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Camera ID</label>
                                    <input type="text" name="camera_id" class="form-control"
                                        placeholder="UGREEN / Hikvision / /dev/video0" data-bs-toggle="tooltip"
                                        title="Merk atau ID hardware kamera">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Threshold Full (%) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="threshold_full" class="form-control" value="90" min="50"
                                        max="100" required data-bs-toggle="tooltip"
                                        title="Persentase kapasitas sebelum status berubah jadi Full">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Target AI Model</label>
                                    <select name="ai_model_version" class="form-select" id="ai-model-select"
                                        data-bs-toggle="tooltip" title="Pilih versi model AI (opsional)">
                                        <option value="">Default (best.pt)</option>
                                        <!-- Dynamic options loaded via JS -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-key me-1"></i>Register & Generate API Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal (API Key Display) -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="ti tabler-check me-2"></i>Device Registered Successfully!</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="ti tabler-alert-triangle me-1"></i>
                    <strong>Important:</strong> Copy the API Key below. It will NOT be shown again!
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Device ID</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="success-device-id" readonly>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="copyToClipboard('success-device-id')" data-bs-toggle="tooltip"
                            title="Copy Device ID">
                            <i class="ti tabler-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">API Key <span class="badge bg-danger">Show Once</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" id="success-api-key" readonly>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="copyToClipboard('success-api-key')" data-bs-toggle="tooltip" title="Copy API Key">
                            <i class="ti tabler-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="downloadConfig()" data-bs-toggle="tooltip"
                        title="Download device configuration as JSON file">
                        <i class="ti tabler-download me-1"></i>Download Config File (.json)
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="window.refreshPage()">
                    <i class="ti tabler-check me-1"></i>Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>