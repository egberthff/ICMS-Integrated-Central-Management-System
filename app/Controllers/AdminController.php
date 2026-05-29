<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\UserRoleModel;
use App\Models\RolePermissionModel;
use App\Controllers\BaseApiController;

class AdminController extends BaseApiController
{

    // POST /api/v1/admin/users/create
    // Adding new user endpoint
    public function createUser()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $username = $data['username'];
        $password = $data['password'];

        if (!$username || !$password) {
            return $this->apiValidationError([
                'username' => 'Username is required',
                'password' => 'Password is required'
            ]);
        }

        $userModel = new UserModel();
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        if ($userModel->createUser($username, $passwordHash)) {
            return $this->apiCreated([
                'username' => $username
            ], 'User created successfully.');
        } else {
            return $this->apiBadRequest('User creation failed. Username may already exist.');
        }
    }

    // DELETE /api/v1/admin/users/{userId}
    // Deleting user endpoint
    public function deleteUser($userId)
    {
        $userModel = new UserModel();
        if ($userModel->deleteUser($userId)) {
            return $this->apiSuccess([
                'user_id' => $userId
            ], 200);
        } else {
            return $this->apiBadRequest('User deletion failed. User may not exist.');
        }
    }


    // POST /api/v1/admin/roles/assign
    // Assign a role to a user
    public function assignRoleToUser()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $userId = $data['user_id'] ?? null;
        $roleId = $data['role_id'] ?? null;

        if (!$userId || !$roleId) {
            return $this->apiValidationError([
                'user_id' => 'user_id is required',
                'role_id' => 'role_id is required'
            ]);
        }

        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $userRoleModel = new UserRoleModel();

        // Verify user exists
        $user = $userModel->find($userId);
        if (!$user) {
            return $this->apiNotFound('User not found.');
        }

        // Verify role exists
        $role = $roleModel->find($roleId);
        if (!$role) {
            return $this->apiNotFound('Role not found.');
        }

        // Assign role to user
        if ($userRoleModel->assignRoleToUser($userId, $roleId)) {
            return $this->apiSuccess([
                'user_id' => $userId,
                'role_id' => $roleId
            ], 200);
        } else {
            return $this->apiBadRequest('Role assignment failed. User may already have this role.');
        }
    }

    // POST /api/v1/admin/roles/revoke
    // Remove a role from a user
    public function removeRoleFromUser()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $userId = $data['user_id'] ?? null;
        $roleId = $data['role_id'] ?? null;

        if (!$userId || !$roleId) {
            return $this->apiValidationError([
                'user_id' => 'user_id is required',
                'role_id' => 'role_id is required'
            ]);
        }

        $userRoleModel = new UserRoleModel();

        if ($userRoleModel->removeRoleFromUser($userId, $roleId)) {
            return $this->apiSuccess([
                'user_id' => $userId,
                'role_id' => $roleId
            ], 200);
        } else {
            return $this->apiBadRequest('Role removal failed. User may not have this role.');
        }
    }

    // POST /api/v1/admin/permissions/assign
    // Assign a permission to a role
    public function assignPermissionToRole()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $roleId = $data['role_id'] ?? null;
        $permissionId = $data['permission_id'] ?? null;

        if (!$roleId || !$permissionId) {
            return $this->apiValidationError([
                'role_id' => 'role_id is required',
                'permission_id' => 'permission_id is required'
            ]);
        }

        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();

        // Verify role exists
        $role = $roleModel->find($roleId);
        if (!$role) {
            return $this->apiNotFound('Role not found.');
        }

        // Verify permission exists
        $permission = $permissionModel->find($permissionId);
        if (!$permission) {
            return $this->apiNotFound('Permission not found.');
        }

        // Assign permission to role
        if ($rolePermissionModel->assignPermissionToRole($roleId, $permissionId)) {
            return $this->apiSuccess([
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ], 200);
        } else {
            return $this->apiBadRequest('Permission assignment failed. Role may already have this permission.');
        }
    }

    // POST /api/v1/admin/permissions/revoke
    // Remove a permission from a role
    public function removePermissionFromRole()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $roleId = $data['role_id'] ?? null;
        $permissionId = $data['permission_id'] ?? null;

        if (!$roleId || !$permissionId) {
            return $this->apiValidationError([
                'role_id' => 'role_id is required',
                'permission_id' => 'permission_id is required'
            ]);
        }

        $rolePermissionModel = new RolePermissionModel();

        if ($rolePermissionModel->removePermissionFromRole($roleId, $permissionId)) {
            return $this->apiSuccess([
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ], 200);
        } else {
            return $this->apiBadRequest('Permission removal failed. Role may not have this permission.');
        }
    }

    // GET /api/v1/admin/users/{userId}/roles
    // Get all roles for a user
    public function getUserRoles($userId)
    {
        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();

        // Verify user exists
        $user = $userModel->find($userId);
        if (!$user) {
            return $this->apiNotFound('User not found.');
        }

        $roles = $userRoleModel->getUserRoles($userId);

        return $this->apiSuccess([
            'user_id' => $userId,
            'roles' => $roles
        ]);
    }

    // GET /api/v1/admin/roles/{roleId}/permissions
    // Get all permissions for a role
    public function getRolePermissions($roleId)
    {
        $roleModel = new RoleModel();
        $rolePermissionModel = new RolePermissionModel();

        // Verify role exists
        $role = $roleModel->find($roleId);
        if (!$role) {
            return $this->apiNotFound('Role not found.');
        }

        $permissions = $rolePermissionModel->getRolePermissions($roleId);

        return $this->apiSuccess([
            'role_id' => $roleId,
            'permissions' => $permissions
        ]);
    }

    // GET /api/v1/admin/roles
    // List all roles
    public function listRoles()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();

        return $this->apiSuccess(['roles' => $roles]);
    }

    // POST /api/v1/admin/roles/create
    // Create a new role
    public function saveRole()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $action = $data['action'] ?? null;
        $roleId = $data['role_id'] ?? null;
        $criticalityLevel = $data['criticality_level'] ?? 1;
        $roleName = $data['role_name'] ?? null;

        if (!$roleName && $action !== 'delete') {
            return $this->apiValidationError(['role_name' => 'role_name is required']);
        }

        if ($action === 'delete' && !$roleId) {
            return $this->apiValidationError(['role_id' => 'role_id is required for deletion']);
        }

        $roleModel = new RoleModel();

        if ($roleModel->saveRoleData($roleName, $criticalityLevel, $roleId, $action)) {
            return $this->apiCreated([
                'role_name' => $roleName,
                'criticality_level' => $criticalityLevel
            ], 200);
        } else {
            return $this->apiBadRequest('Role save role. It may already exist.');
        }
    }

    public function updateRole()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $roleId = $data['role_id'];
        $roleName = $data['role_name'] ?? null;
        $criticalityLevel = $data['criticality_level'] ?? 1;

        if (!$roleName) {
            return $this->apiValidationError(['role_name' => 'role_name is required']);
        }

        $updateData = [
            'role_id' => $roleId,
            'role_name' => $roleName
        ];

        $roleModel = new RoleModel();

        if ($roleModel->updateRole($roleId, $updateData)) {
            return $this->apiCreated([
                'role_name' => $roleName,
                'criticality_level' => $criticalityLevel
            ], 200);
        } else {
            return $this->apiBadRequest('Role creation failed. Role may already exist.');
        }
    }

    // GET /api/v1/admin/permissions
    // List all permissions
    public function listPermissions()
    {
        $permissionModel = new PermissionModel();
        $permissions = $permissionModel->findAll();

        return $this->apiSuccess(['permissions' => $permissions]);
    }

    // POST /api/v1/admin/permissions/create
    // Create a new permission
    public function createPermission()
    {
        $data = $this->getJsonData();

        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $permissionKey = $data['permission_key'] ?? null;
        $description = $data['description'] ?? '';

        if (!$permissionKey) {
            return $this->apiValidationError(['permission_key' => 'permission_key is required']);
        }

        $permissionModel = new PermissionModel();
        if ($permissionModel->createPermission($permissionKey, $description)) {
            return $this->apiCreated([
                'permission_key' => $permissionKey,
                'description' => $description
            ], 200);
        } else {
            return $this->apiBadRequest('Permission creation failed. Permission may already exist.');
        }
    }

    // DELETE /api/v1/admin/permissions/{permissionId}
    // Delete a permission
    public function deletePermission($permissionId)
    {
        $permissionModel = new PermissionModel();

        if ($permissionModel->deletePermission($permissionId)) {
            return $this->apiSuccess([], 200);
        } else {
            return $this->apiBadRequest('Permission deletion failed. Permission may not exist.');
        }
    }

    // GET /api/v1/admin/users
    // List all users
    public function listUsers()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        return $this->apiSuccess(['users' => $users]);
    }
}
