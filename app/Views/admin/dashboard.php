<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row">
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <!-- Optional functional visual anchor icon -->
                <svg xmlns="http://w3.org" width="24" height="24" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill me-2" viewBox="0 0 16 16">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                <div>
                        <?= esc(session()->getFlashdata('error')) ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-people display-1 text-primary"></i>
                <h5 class="card-title mt-2">Total Users</h5>
                <p class="card-text display-4" id="totalUsers">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-shield-check display-1 text-success"></i>
                <h5 class="card-title mt-2">Total Roles</h5>
                <p class="card-text display-4" id="totalRoles">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-lock display-1 text-warning"></i>
                <h5 class="card-title mt-2">Total Permissions</h5>
                <p class="card-text display-4" id="totalPermissions">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-person-check display-1 text-info"></i>
                <h5 class="card-title mt-2">Your Role</h5>
                <p class="card-text" id="yourRole">-</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Admin Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="/admin/users" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Manage Users</a>
                <a href="/admin/roles" class="btn btn-success me-2"><i class="bi bi-plus-circle"></i> Manage Roles</a>
                <a href="/admin/permissions" class="btn btn-warning"><i class="bi bi-plus-circle"></i> Manage
                    Permissions</a>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadDashboardStats() {
        try {
            // Load users count
            const usersResponse = await apiCall('/api/v1/admin/users', 'GET');
            if (usersResponse.ok && usersResponse.data && usersResponse.data.data) {
                document.getElementById('totalUsers').textContent = usersResponse.data.data.length;
            }

            // Load roles count
            const rolesResponse = await apiCall('/api/v1/admin/roles', 'GET');
            if (rolesResponse.ok && rolesResponse.data && rolesResponse.data.data) {
                document.getElementById('totalRoles').textContent = rolesResponse.data.data.length;
            }

            // Load permissions count
            const permsResponse = await apiCall('/api/v1/admin/permissions', 'GET');
            if (permsResponse.ok && permsResponse.data && permsResponse.data.data) {
                document.getElementById('totalPermissions').textContent = permsResponse.data.data.length;
            }

            // Load current user role
            const userRole = localStorage.getItem('activeRole') || 'admin';
            document.getElementById('yourRole').textContent = userRole.toUpperCase();
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    window.addEventListener('load', loadDashboardStats);
</script>

<?= $this->endSection() ?>