<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-users me-2"></i>User & Tenants Management
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-danger d-none" id="delete-selected-btn"
                        onclick="userManagement.showBulkDeleteModal()">
                        <i class="ti tabler-trash me-1"></i>Delete Selected (<span id="selected-count">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-primary"
                        onclick="window.spaNavigator.loadPage('assignments', '/dashboard/assignments')">
                        <i class="ti tabler-subtask me-1"></i>Assignments
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createUserModal">
                        <i class="ti tabler-plus me-1"></i>Add User
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="search-input-wrapper">
                            <i class="ti tabler-search search-icon"></i>
                            <input type="text" id="user-search" class="form-control" placeholder="Search users...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="role-filter" class="form-select">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="user">User</option>
                            <option value="tenan">Tenant</option>
                            <option value="teknisi">Technician</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="status-filter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-label-secondary w-100"
                            onclick="userManagement.refreshData()">
                            <i class="ti tabler-refresh"></i>
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card stats-card stats-card-primary mb-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ti tabler-users"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="total-users">0</h5>
                                        <small>Total Users</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        <h5 class="mb-0" id="active-users">0</h5>
                                        <small>Active</small>
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
                                            <i class="ti tabler-building-store"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="total-tenants">0</h5>
                                        <small>Tenants</small>
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
                                            <i class="ti tabler-user-plus"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="new-today">0</h5>
                                        <small>New Today</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- Skeleton loader -->
                            <tr>
                                <td colspan="8">
                                    <div class="skeleton skeleton-card"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Users pagination" class="mt-3">
                    <ul class="pagination justify-content-end" id="users-pagination">
                        <!-- Dynamic pagination -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- User Detail Modal -->
<div class="modal fade" id="userDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="user-detail-content">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="create-user-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="tenan">Tenant</option>
                            <option value="teknisi">Technician</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Points</label>
                        <input type="number" name="points_balance" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="edit-user-form">
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="edit-user-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit-user-email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep
                                current)</small></label>
                        <input type="password" name="password" class="form-control"
                            placeholder="Leave blank to keep current password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="edit-user-role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="tenan">Tenant</option>
                            <option value="teknisi">Technician</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points Balance</label>
                        <input type="number" name="points_balance" id="edit-user-points" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentModalLabel">
                    <i class="ti tabler-map-pin me-2"></i>RVM Installation Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column: Selections -->
                    <div class="col-md-5">
                        <!-- Tag-Based User Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ti tabler-users me-1"></i>Assign To (Technicians/Operators)
                            </label>
                            <div class="tag-input-container">
                                <div id="selected-users" class="tag-list"></div>
                                <input type="text" id="user-search-input" class="form-control"
                                    placeholder="Type name to search..." autocomplete="off">
                                <div id="user-suggestions" class="autocomplete-dropdown" style="display:none;"></div>
                            </div>
                            <small class="text-muted">Type and select users to assign</small>
                        </div>

                        <!-- Tag-Based Machine Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ti tabler-device-desktop me-1"></i>RVM Machines
                            </label>
                            <div class="tag-input-container">
                                <div id="selected-machines" class="tag-list"></div>
                                <input type="text" id="machine-search-input" class="form-control"
                                    placeholder="Type machine name or serial..." autocomplete="off">
                                <div id="machine-suggestions" class="autocomplete-dropdown" style="display:none;"></div>
                            </div>
                            <small class="text-muted">Select machines to be installed</small>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="ti tabler-notes me-1"></i>Notes (Optional)
                            </label>
                            <textarea id="assignment-notes" class="form-control" rows="3"
                                placeholder="Additional instructions or notes..."></textarea>
                        </div>

                        <!-- Coordinates Display -->
                        <div class="coordinates-display">
                            <div class="row g-2">
                                <div class="col-12 mb-2">
                                    <label class="form-label">Address</label>
                                    <input type="text" id="assignment-address" class="form-control form-control-sm"
                                        readonly placeholder="Click map or search to get address">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" id="assignment-lat" class="form-control form-control-sm"
                                        readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" id="assignment-lng" class="form-control form-control-sm"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Map -->
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">
                            <i class="ti tabler-map me-1"></i>Installation Location
                        </label>

                        <!-- Map Search Bar -->
                        <div class="input-group mb-2 map-search-bar">
                            <span class="input-group-text"><i class="ti tabler-search"></i></span>
                            <input type="text" id="location-search" class="form-control"
                                placeholder="Search location (e.g., Jogja City Mall)">
                            <button class="btn btn-primary" type="button" id="search-location-btn">
                                <i class="ti tabler-search"></i> Search
                            </button>
                        </div>

                        <!-- Interactive Map -->
                        <div id="assignment-map" style="height: 380px;"></div>
                        <small class="text-muted mt-1 d-block">
                            <i class="ti tabler-info-circle"></i>
                            Click map or drag marker to set exact location. Use search to find places.
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-assignment-btn"
                    onclick="userManagement.submitAssignment()">
                    <i class="ti tabler-check me-1"></i>Create Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="ti tabler-alert-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="ti tabler-alert-circle me-1"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>

                <p id="delete-confirm-message">Are you sure you want to delete this user?</p>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Enter your password to confirm:</label>
                    <input type="password" id="delete-confirm-password" class="form-control"
                        placeholder="Your admin password" autocomplete="new-password">
                    <div class="invalid-feedback" id="password-error">Invalid password</div>
                </div>

                <input type="hidden" id="delete-user-ids" value="">
                <input type="hidden" id="delete-mode" value="single">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn"
                    onclick="userManagement.confirmDelete()">
                    <i class="ti tabler-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>