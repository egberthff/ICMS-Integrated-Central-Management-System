<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Permissions Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
            <i class="bi bi-plus-circle"></i> Create Permission
        </button>
    </div>
</div>

<!-- Permissions Table -->
<div class="card">
    <div class="card-body">
        <table class="table table-hover" id="permissionsTable">
            <thead>
                <tr>
                    <th>Permission Key</th>
                    <th>Description</th>
                    <th>Used By Roles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="permissionsTableBody">
                <tr>
                    <td colspan="4" class="text-center text-muted">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPermissionForm">
                    <div class="mb-3">
                        <label for="permissionKey" class="form-label">Permission Key</label>
                        <input type="text" class="form-control" id="permissionKey" placeholder="e.g., payroll:execute" required>
                        <small class="form-text text-muted">Use format: module:action (e.g., payroll:execute, profile:read)</small>
                    </div>
                    <div class="mb-3">
                        <label for="permissionDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="permissionDescription" placeholder="Brief description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createPermission()">Create Permission</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadPermissions() {
        try {
            const response = await apiCall('/api/v1/admin/permissions', 'GET');
            const tbody = document.getElementById('permissionsTableBody');
            
            if (response.ok && response.data) {
                tbody.innerHTML = '';
                
                for (const perm of response.data.data) {
                    const rolesUsing = await getPermissionRoles(perm.permission_id);
                    
                    const row = `
                        <tr>
                            <td><code>${perm.permission_key}</code></td>
                            <td>${perm.description || '-'}</td>
                            <td>
                                ${rolesUsing.length > 0 
                                    ? rolesUsing.join(', ')
                                    : '<span class="text-muted">Not assigned</span>'
                                }
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deletePermission('${perm.permission_id}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No permissions found</td></tr>';
            }
        } catch (error) {
            console.error('Error loading permissions:', error);
        }
    }

    async function getPermissionRoles(permissionId) {
        try {
            const response = await apiCall('/api/v1/admin/roles', 'GET');
            const rolesUsing = [];
            
            if (response.ok && response.data) {
                for (const role of response.data.data) {
                    const permsResponse = await apiCall(`/api/v1/admin/roles/${role.role_id}/permissions`, 'GET');
                    if (permsResponse.ok && permsResponse.data.permissions.some(p => p.permission_id === permissionId)) {
                        rolesUsing.push(role.role_name);
                    }
                }
            }
            
            return rolesUsing;
        } catch (error) {
            console.error('Error getting permission roles:', error);
            return [];
        }
    }

    async function createPermission() {
        const permissionKey = document.getElementById('permissionKey').value;
        const description = document.getElementById('permissionDescription').value;

        if (!permissionKey) {
            showAlert('Permission key is required', 'warning');
            return;
        }

        try {
            const response = await apiCall('/api/v1/admin/permissions/create', 'POST', {
                permission_key: permissionKey,
                description: description
            });

            if (response.ok) {
                showAlert('Permission created successfully!', 'success');
                document.getElementById('createPermissionForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('createPermissionModal')).hide();
                loadPermissions();
            } else {
                showAlert(response.data.message || 'Failed to create permission', 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    async function deletePermission(permissionId) {
        if (!confirm('Are you sure you want to delete this permission?')) return;

        try {
            const response = await apiCall(`/api/v1/admin/permissions/${permissionId}`, 'DELETE');

            if (response.ok) {
                showAlert('Permission deleted successfully!', 'success');
                loadPermissions();
            } else {
                showAlert(response.data.message || 'Failed to delete permission', 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    window.addEventListener('load', loadPermissions);
</script>

<?= $this->endSection() ?>
