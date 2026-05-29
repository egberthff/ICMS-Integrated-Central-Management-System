<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Roles Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-action="add" data-bs-target="#createRoleModal"
            onclick="openManageRoleModal(null, null, this)">
            <i class="bi bi-plus-circle"></i> Create Role
        </button>
    </div>
</div>

<!-- Roles Table -->
<div class="card">
    <div class="card-body">
        <table class="table table-hover" id="rolesTable">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Criticality Level</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rolesTableBody">
                <tr>
                    <td colspan="4" class="text-center text-muted">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createRoleForm">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="roleName" placeholder="e.g., admin, manager"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="criticalityLevel" class="form-label">Criticality Level</label>
                        <select class="form-select" id="criticalityLevel" required>
                            <option value="1">1 - Low</option>
                            <option value="3">3 - Medium</option>
                            <option value="5">5 - High</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Role</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Permission Modal -->
<div class="modal fade" id="assignPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Permission to Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignPermissionForm">
                    <input type="hidden" id="assignRoleId">
                    <div class="mb-3">
                        <label for="assignPermissionSelect" class="form-label">Select Permission</label>
                        <select class="form-select" id="assignPermissionSelect" required>
                            <option value="">Loading permissions...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignPermission()">Assign Permission</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadRoles() {
        try {
            const response = await apiCall('/api/v1/admin/roles', 'GET');
            const tbody = document.getElementById('rolesTableBody');

            if (response.ok && response.data) {
                tbody.innerHTML = '';

                for (const role of response.data.data.roles) {
                    const permsResponse = await apiCall(`/api/v1/admin/roles/${role.role_id}/permissions`, 'GET');
                    const permissions = permsResponse.ok ? permsResponse.data.data.permissions.map(p => p.permission_key).join(', ') : '-';

                    const row = `
                        <tr>
                            <td><strong>${role.role_name}</strong></td>
                            <td>
                                <span class="badge ${getCriticalityColor(role.criticality_level)}">
                                    Level ${role.criticality_level}
                                </span>
                            </td>
                            <td>${permissions || 'No permissions'}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-action="assign" onclick="openAssignPermissionModal('${role.role_id}', '${role.role_name}')">
                                    <i class="bi bi-plus"></i> Add Permission
                                </button>
                                 <button class="btn btn-sm btn-warning" data-action="revoke" onclick="openAssignPermissionModal('${role.role_id}', '${role.role_name}')">
                                    <i class="bi bi-dash"></i> Remove Permission
                                </button>
                                <button class="btn btn-sm btn-success" data-action="edit" data-bs-toggle="modal" data-bs-target="#createRoleModal" onclick="openManageRoleModal('${role.role_id}', '${role.role_name}', this)">
                                    <i class="bi bi-plus"></i> Edit
                                </button>
                                 <button class="btn btn-sm btn-danger" data-action="delete" onclick="saveRole('delete','${role.role_id}')">
                                    <i class="bi bi-trash"></i> Remove role
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No roles found</td></tr>';
            }
        } catch (error) {
            console.error('Error loading roles:', error);
        }
    }

    function getCriticalityColor(level) {
        if (level <= 1) return 'bg-success';
        if (level <= 3) return 'bg-warning';
        return 'bg-danger';
    }

    async function loadPermissionsForAssignment() {
        try {
            const response = await apiCall('/api/v1/admin/permissions', 'GET');
            const select = document.getElementById('assignPermissionSelect');
            select.innerHTML = '<option value="">Select a permission</option>';

            if (response.ok && response.data.data) {
                for (const perm of response.data.data.permissions) {
                    select.innerHTML += `<option value="${perm.permission_id}">${perm.permission_key}</option>`;
                }
            }
        } catch (error) {
            console.error('Error loading permissions:', error);
        }
    }

    function openAssignPermissionModal(roleId, roleName) {
        document.getElementById('assignRoleId').value = roleId;
        loadPermissionsForAssignment();
        const modal = new bootstrap.Modal(document.getElementById('assignPermissionModal'));
        modal.show();
        const action = event.target.getAttribute('data-action') || 'assign'; // Default to assign if not specified
        document.querySelector('#assignPermissionModal .modal-title').textContent = action === 'assign'
            ? `Assign Permission to ${roleName}`
            : `Remove Permission from ${roleName}`;
        document.querySelector('#assignPermissionModal button.btn-primary').textContent = action === 'assign'
            ? 'Assign Permission'
            : 'Remove Permission';
        document.querySelector('#assignPermissionModal button.btn-primary').setAttribute('onclick', `assignPermission('${action}')`);
    }

    function openManageRoleModal(roleId = null, roleName = null, el) {
        const action = el ? el.dataset.action : 'add';
        const modalTitle = document.querySelector('#createRoleModal .modal-title');
        const submitBtn = document.querySelector('#createRoleModal .btn-primary');
        document.getElementById('roleName').value = roleName;

        if (action === 'add') {
            modalTitle.textContent = "Create New Role";
            submitBtn.textContent = "Create Role";
            submitBtn.setAttribute('onclick', `saveRole('${action}')`);

        } else if (action === 'edit') {
            modalTitle.textContent = "Edit Role";
            submitBtn.textContent = "Update Role";
            submitBtn.setAttribute('onclick', `saveRole('${action}', '${roleId}')`);

        }
    }


    async function saveRole(action, roleId = null) {
        const roleName = document.getElementById('roleName').value;
        const criticalityLevel = document.getElementById('criticalityLevel').value;

        if (action !== 'delete') {
            if (!roleName) {
                showAlert('Please fill all fields', 'warning');
                return;
            }
        }

        const payload = {
            role_name: roleName,
            criticality_level: parseInt(criticalityLevel),
            action: action
        };

        if ((action === 'edit' || action === 'delete') && roleId) {
            payload.role_id = roleId;
        }

        try {
            const response = await apiCall('/api/v1/admin/roles/save', 'POST', payload);

            if (response.ok) {
                let msg = `Role successfully ${action === 'edit' ? 'updated' : action === 'delete' ? 'deleted' : 'created'}!`;
                showAlert(msg, 'success');
                // Reset form if it was a creation/edit action
                if (action !== 'delete') {
                    document.getElementById('createRoleForm').reset();
                }
                const modalEl = document.getElementById('createRoleModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                loadRoles();
            } else {
                showAlert(response.data.message || `Failed to ${action} role`, 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    async function assignPermission(action) {
        const roleId = document.getElementById('assignRoleId').value;
        const permissionId = document.getElementById('assignPermissionSelect').value;

        if (!roleId || !permissionId) {
            showAlert('Please select a permission', 'warning');
            return;
        }

        try {
            const response = await apiCall(`/api/v1/admin/permissions/${action}`, 'POST', {
                role_id: roleId,
                permission_id: permissionId
            });

            if (response.ok) {
                showAlert(`Permission ${action}ed successfully!`, 'success');
                bootstrap.Modal.getInstance(document.getElementById('assignPermissionModal')).hide();
                loadRoles();
            } else {
                showAlert(response.data.message || `Failed to ${action} permission`, 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    window.addEventListener('load', loadRoles);
</script>

<?= $this->endSection() ?>