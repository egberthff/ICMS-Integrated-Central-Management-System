<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2>Switch Role</h2>
        <p class="text-muted">Select a role to switch into. This will simulate your permissions as if you were that
            role.</p>
        <div id="switchRoleAlert" class="alert d-none" role="alert"></div>
        <div class="list-group" id="rolesList">

            <div class="text-center text-muted py-3">Loading roles...</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadRolesForSwitching();
    })
    async function loadRolesForSwitching() {
        try {
            let userId = localStorage.getItem('user_id');
            const response = await fetch(`user/${userId}/roles`);
            const data = await response.json();
            const rolesList = document.getElementById('rolesList');
            rolesList.innerHTML = '';

            if (data.data.roles && data.data.roles.length > 0) {
                data.data.roles.forEach(role => {
                    const roleItem = document.createElement('button');
                    roleItem.className = 'list-group-item list-group-item-action';
                    roleItem.textContent = `${role.role_name} (Criticality: ${role.criticality_level})`;
                    roleItem.onclick = () => switchRole(role.role_name);
                    rolesList.appendChild(roleItem);
                });
            } else {
                rolesList.innerHTML = '<div class="text-center text-muted py-3">No roles available for switching.</div>';
            }
        } catch (error) {
            console.error('Error loading roles:', error);
            const alertBox = document.getElementById('switchRoleAlert');
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'Failed to load roles. Please try again later.';
            alertBox.classList.remove('d-none');
        }
    }

    async function switchRole(roleName) {
        if (!confirm(`Are you sure you want to switch to the "${roleName}" role?`)) {
            return;
        }
        try {
            const response = await fetch('/api/auth/switch-role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // 'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    target_role: roleName,
                    mfa_token: '123456' // In a real implementation, you would prompt the user for an MFA token if the target role is critical
                })
            });

            const data = await response.json();
            const alertBox = document.getElementById('switchRoleAlert');
            if (response.ok) {
                alertBox.className = 'alert alert-success';
                alertBox.textContent = `Successfully switched to "${roleName}" role. Redirecting to dashboard...`;
                localStorage.setItem('activeRole', roleName);

                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1000);
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = data.message || 'Failed to switch roles. Please try again.';
            }
            alertBox.classList.remove('d-none');
        } catch (error) {
            console.error('Error switching roles:', error);
            const alertBox = document.getElementById('switchRoleAlert');
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'An error occurred while switching roles. Please try again later.';
            alertBox.classList.remove('d-none');
        }
    }
</script>

<?= $this->endSection() ?>