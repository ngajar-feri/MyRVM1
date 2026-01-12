<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-device-desktop-analytics me-2"></i>RVM Machines Management
                </h5>
                <div>
                    <button type="button" class="btn btn-label-primary me-2" id="toggle-view">
                        <i class="ti tabler-layout-grid"></i>
                    </button>
                    <button type="button" class="btn btn-primary">
                        <i class="ti tabler-plus me-1"></i>Add Machine
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <select id="status-filter" class="form-select">
                            <option value="">All Status</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="location-filter" class="form-control"
                            placeholder="Filter by location...">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-label-secondary w-100" onclick="window.refreshPage()">
                            <i class="ti tabler-refresh me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card stats-card stats-card-success mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="ti tabler-check"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="online-count">0</h5>
                                        <small>Online</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stats-card stats-card-danger mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2">
                                        <span class="avatar-initial rounded bg-label-danger">
                                            <i class="ti tabler-x"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="offline-count">0</h5>
                                        <small>Offline</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stats-card stats-card-warning mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="ti tabler-tool"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="maintenance-count">0</h5>
                                        <small>Maintenance</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stats-card stats-card-primary mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ti tabler-recycle"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="total-transactions">0</h5>
                                        <small>Total Transactions</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div class="row g-3" id="machines-grid">
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

<!-- Machine Detail Modal -->
<div class="modal fade" id="machineDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="machine-name">Machine Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="machine-detail-content">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>