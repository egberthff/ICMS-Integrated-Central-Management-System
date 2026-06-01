<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row">
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
                <h5 class="mb-0">Quick Actions</h5>
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
            if (usersResponse.ok && usersResponse.data.data && usersResponse.data.data.users) {
                document.getElementById('totalUsers').textContent = usersResponse.data.data.users.length;
            }

            // Load roles count
            const rolesResponse = await apiCall('/api/v1/admin/roles', 'GET');
            if (rolesResponse.ok && rolesResponse.data.data && rolesResponse.data.data.roles) {
                document.getElementById('totalRoles').textContent = rolesResponse.data.data.roles.length;
            }

            // Load permissions count
            const permsResponse = await apiCall('/api/v1/admin/permissions', 'GET');
            if (permsResponse.ok && permsResponse.data.data && permsResponse.data.data.permissions) {
                document.getElementById('totalPermissions').textContent = permsResponse.data.data.permissions.length;
            }

            // Load current user role
            const userRole = localStorage.getItem('activeRole') || 'employee';
            document.getElementById('yourRole').textContent = userRole.toUpperCase();
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    window.addEventListener('load', loadDashboardStats);
</script>

<?= $this->endSection() ?>