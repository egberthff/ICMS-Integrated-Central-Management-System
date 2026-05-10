<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-people-fill display-1 text-primary"></i>
                <h5 class="card-title mt-2">Team Members</h5>
                <p class="card-text display-4" id="teamCount">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <h5 class="card-title mt-2">Pending Approvals</h5>
                <p class="card-text display-4" id="pendingApprovals">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-graph-up display-1 text-info"></i>
                <h5 class="card-title mt-2">Monthly Report</h5>
                <p class="card-text">View Performance</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Manager Quick Actions</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary me-2"><i class="bi bi-check"></i> Approve Timesheets</button>
                <button class="btn btn-success me-2"><i class="bi bi-people"></i> Manage Team</button>
                <button class="btn btn-info me-2"><i class="bi bi-bar-chart"></i> View Reports</button>
                <button class="btn btn-warning"><i class="bi bi-calendar"></i> Schedule Meetings</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadManagerStats() {
        try {
            // Load team count (placeholder)
            document.getElementById('teamCount').textContent = '5'; // Replace with actual API call

            // Load pending approvals (placeholder)
            document.getElementById('pendingApprovals').textContent = '3'; // Replace with actual API call
        } catch (error) {
            console.error('Error loading manager stats:', error);
        }
    }

    window.addEventListener('load', loadManagerStats);
</script>

<?= $this->endSection() ?>