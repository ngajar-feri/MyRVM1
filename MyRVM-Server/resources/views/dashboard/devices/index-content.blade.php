<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-cpu me-2"></i>Edge Devices Management
                </h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#registerDeviceModal">
                    <i class="ti tabler-plus me-1"></i>Register Device
                </button>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <select id="device-status-filter" class="form-select">
                            <option value="">All Devices</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-label-secondary w-100" onclick="window.refreshPage()">
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

<!-- Register Device Modal -->
<div class="modal fade" id="registerDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register New Edge Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="register-device-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Device Serial</label>
                        <input type="text" name="device_serial" class="form-control" placeholder="JETSON-XXX-XXX"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RVM Machine</label>
                        <select name="rvm_id" class="form-select" required>
                            <option value="">Select RVM...</option>
                            <!-- Dynamic options -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tailscale IP</label>
                        <input type="text" name="tailscale_ip" class="form-control" placeholder="100.64.0.x">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Device</button>
                </div>
            </form>
        </div>
    </div>
</div>