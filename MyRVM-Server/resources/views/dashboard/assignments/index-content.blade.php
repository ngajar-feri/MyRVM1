{{-- Assignment List Page --}}
<div id="assignments-page" class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Management /</span> Assignments
    </h4>

    {{-- Filters and Actions --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                <button type="button" class="btn btn-outline-secondary" data-filter="pending">Pending</button>
                <button type="button" class="btn btn-outline-warning" data-filter="in_progress">In Progress</button>
                <button type="button" class="btn btn-outline-success" data-filter="completed">Completed</button>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-icon btn-primary" onclick="window.assignmentManager.loadAssignments()"
                title="Refresh">
                <i class="ti tabler-refresh"></i>
            </button>
        </div>
    </div>

    {{-- Assignment Table Card --}}
    <div class="card">
        <h5 class="card-header">Assignment List</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="assignments-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Task Details</th>
                        <th>Team</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0" id="assignments-tbody">
                    {{-- Skeleton Loader --}}
                    <tr class="skeleton-row">
                        <td colspan="6">
                            <div class="d-flex justify-content-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer">
            <nav aria-label="Assignment pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0" id="assignments-pagination">
                    {{-- Pagination will be injected here --}}
                </ul>
            </nav>
        </div>
    </div>

</div>
</div>