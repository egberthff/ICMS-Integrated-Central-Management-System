<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Users Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-plus-circle"></i> Create User
        </button>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <table class="table table-hover" id="usersTable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <tr>
                    <td colspan="4" class="text-center text-muted">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label for="newUsername" class="form-label">Username (Email)</label>
                        <input type="email" class="form-control" id="newUsername" placeholder="user@company.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="newPassword" placeholder="Enter password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">Create User</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div class="modal fade" id="assignRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignRoleForm">
                    <input type="hidden" id="assignUserId">
                    <div class="mb-3">
                        <label for="assignRoleSelect" class="form-label">Select Role</label>
                        <select class="form-select" id="assignRoleSelect" required>
                            <option value="">Loading roles...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignRole()">Assign Role</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadUsers() {
        try {
            const response = await apiCall('/api/v1/admin/users', 'GET');
            const tbody = document.getElementById('usersTableBody');
            
            if (response.ok && response.data) {
                tbody.innerHTML = '';
                
                for (const user of response.data) {
                    const rolesResponse = await apiCall(`/api/v1/admin/users/${user.user_id}/roles`, 'GET');
                    const roles = rolesResponse.ok ? rolesResponse.data.roles.map(r => r.role_name).join(', ') : '-';
                    
                    const row = `
                        <tr>
                            <td>${user.username}</td>
                            <td>${roles || 'No roles'}</td>
                            <td>
                                <span class="badge ${user.is_active ? 'bg-success' : 'bg-danger'}">
                                    ${user.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="openAssignRoleModal('${user.user_id}', '${user.username}')">
                                    <i class="bi bi-plus"></i> Add Role
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteUser('${user.user_id}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No users found</td></tr>';
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    async function loadRolesForAssignment() {
        try {
            const response = await apiCall('/api/v1/admin/roles', 'GET');
            const select = document.getElementById('assignRoleSelect');
            select.innerHTML = '<option value="">Select a role</option>';
            
            if (response.ok && response.data) {
                for (const role of response.data) {
                    select.innerHTML += `<option value="${role.role_id}">${role.role_name}</option>`;
                }
            }
        } catch (error) {
            console.error('Error loading roles:', error);
        }
    }

    function openAssignRoleModal(userId, username) {
        document.getElementById('assignUserId').value = userId;
        loadRolesForAssignment();
        const modal = new bootstrap.Modal(document.getElementById('assignRoleModal'));
        modal.show();
    }

    async function createUser() {
        const username = document.getElementById('newUsername').value;
        const password = document.getElementById('newPassword').value;

        if (!username || !password) {
            showAlert('Please fill all fields', 'warning');
            return;
        }

        try {
            const response = await apiCall('/api/v1/admin/users/create', 'POST', {
                username,
                password
            });

            if (response.ok) {
                showAlert('User created successfully!', 'success');
                document.getElementById('createUserForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
                loadUsers();
            } else {
                showAlert(response.data.message || 'Failed to create user', 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    async function assignRole() {
        const userId = document.getElementById('assignUserId').value;
        const roleId = document.getElementById('assignRoleSelect').value;

        if (!userId || !roleId) {
            showAlert('Please select a role', 'warning');
            return;
        }

        try {
            const response = await apiCall('/api/v1/admin/roles/assign', 'POST', {
                user_id: userId,
                role_id: roleId
            });

            if (response.ok) {
                showAlert('Role assigned successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('assignRoleModal')).hide();
                loadUsers();
            } else {
                showAlert(response.data.message || 'Failed to assign role', 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    async function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;

        try {
            const response = await apiCall(`/api/v1/admin/users/${userId}`, 'DELETE');

            if (response.ok) {
                showAlert('User deleted successfully!', 'success');
                loadUsers();
            } else {
                showAlert(response.data.message || 'Failed to delete user', 'danger');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'danger');
        }
    }

    window.addEventListener('load', loadUsers);
</script>

<?= $this->endSection() ?>
