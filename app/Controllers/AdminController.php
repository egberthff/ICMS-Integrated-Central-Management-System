<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\UserRoleModel;
use App\Models\RolePermissionModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class AdminController extends ResourceController
{

    // POST /api/v1/admin/users/create
    // Adding new user endpoint
    public function createUser(){

    try {
            $data = $this->request->getJSON();
        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
            log_message('error', 'Create User JSON parse error: ' . $e->getMessage());
            return $this->failValidationErrors(['message' => 'Invalid JSON payload: ' . $e->getMessage()]);
        }

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $username = $data->username;
        $password = $data->password;
        
        if (!$username || !$password) {
            return $this->failValidationErrors([
                'username' => 'Username is required',
                'password' => 'Password is required'
            ]);
        }

        $userModel = new UserModel();
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        if ($userModel->createUser($username, $passwordHash)) {
            return $this->respondCreated([
                'status' => 201,
                'message' => 'User created successfully.',
                'data' => [
                    'username' => $username
                ]
            ]);
        } else {
            return $this->fail('User creation failed. Username may already exist.', 400);
        }
    }

    // DELETE /api/v1/admin/users/{userId}
    // Deleting user endpoint
    public function deleteUser($userId){
        $userModel = new UserModel();
        if ($userModel->deleteUser($userId)) {
            return $this->respond([
                'status' => 200,
                'message' => 'User deleted successfully.',
                'data' => [
                    'user_id' => $userId
                ]
            ]);
        } else {
            return $this->fail('User deletion failed. User may not exist.', 400);
        }
    }


    // POST /api/v1/admin/roles/assign
    // Assign a role to a user
    public function assignRoleToUser()
    {
        $data = $this->request->getJSON();

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $userId = $data->user_id ?? null;
        $roleId = $data->role_id ?? null;

        if (!$userId || !$roleId) {
            return $this->failValidationErrors([
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
            return $this->failNotFound('User not found.');
        }

        // Verify role exists
        $role = $roleModel->find($roleId);
        if (!$role) {
            return $this->failNotFound('Role not found.');
        }

        // Assign role to user
        if ($userRoleModel->assignRoleToUser($userId, $roleId)) {
            return $this->respond([
                'status'  => 200,
                'message' => 'Role assigned successfully.',
                'data'    => [
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]
            ]);
        } else {
            return $this->fail('Role assignment failed. User may already have this role.', 400);
        }
    }

    // POST /api/v1/admin/roles/revoke
    // Remove a role from a user
    public function removeRoleFromUser()
    {
        $data = $this->request->getJSON();

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $userId = $data->user_id ?? null;
        $roleId = $data->role_id ?? null;

        if (!$userId || !$roleId) {
            return $this->failValidationErrors([
                'user_id' => 'user_id is required',
                'role_id' => 'role_id is required'
            ]);
        }

        $userRoleModel = new UserRoleModel();

        if ($userRoleModel->removeRoleFromUser($userId, $roleId)) {
            return $this->respond([
                'status'  => 200,
                'message' => 'Role removed successfully.',
                'data'    => [
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]
            ]);
        } else {
            return $this->fail('Role removal failed. User may not have this role.', 400);
        }
    }

    // POST /api/v1/admin/permissions/assign
    // Assign a permission to a role
    public function assignPermissionToRole()
    {
        $data = $this->request->getJSON();

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $roleId = $data->role_id ?? null;
        $permissionId = $data->permission_id ?? null;

        if (!$roleId || !$permissionId) {
            return $this->failValidationErrors([
                'role_id'       => 'role_id is required',
                'permission_id' => 'permission_id is required'
            ]);
        }

        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();

        // Verify role exists
        $role = $roleModel->find($roleId);
        if (!$role) {
            return $this->failNotFound('Role not found.');
        }

        // Verify permission exists
        $permission = $permissionModel->find($permissionId);
        if (!$permission) {
            return $this->failNotFound('Permission not found.');
        }

        // Assign permission to role
        if ($rolePermissionModel->assignPermissionToRole($roleId, $permissionId)) {
            return $this->respond([
                'status'  => 200,
                'message' => 'Permission assigned successfully.',
                'data'    => [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId
                ]
            ]);
        } else {
            return $this->fail('Permission assignment failed. Role may already have this permission.', 400);
        }
    }

    // POST /api/v1/admin/permissions/revoke
    // Remove a permission from a role
    public function removePermissionFromRole()
    {
        $data = $this->request->getJSON();

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $roleId = $data->role_id ?? null;
        $permissionId = $data->permission_id ?? null;

        if (!$roleId || !$permissionId) {
            return $this->failValidationErrors([
                'role_id'       => 'role_id is required',
                'permission_id' => 'permission_id is required'
            ]);
        }

        $rolePermissionModel = new RolePermissionModel();

        if ($rolePermissionModel->removePermissionFromRole($roleId, $permissionId)) {
            return $this->respond([
                'status'  => 200,
                'message' => 'Permission removed successfully.',
                'data'    => [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId
                ]
            ]);
        } else {
            return $this->fail('Permission removal failed. Role may not have this permission.', 400);
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
            return $this->failNotFound('User not found.');
        }

        $roles = $userRoleModel->getUserRoles($userId);

        return $this->respond([
            'status' => 200,
            'user_id' => $userId,
            'roles'  => $roles
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
            return $this->failNotFound('Role not found.');
        }

        $permissions = $rolePermissionModel->getRolePermissions($roleId);

        return $this->respond([
            'status'      => 200,
            'role_id'     => $roleId,
            'permissions' => $permissions
        ]);
    }

    // GET /api/v1/admin/roles
    // List all roles
    public function listRoles()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();

        return $this->respond([
            'status' => 200,
            'data'   => $roles
        ]);
    }

    // POST /api/v1/admin/roles/create
    // Create a new role
    public function createRole()
    {
        $data = $this->request->getJSON();

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $roleName = $data->role_name ?? null;
        $criticalityLevel = $data->criticality_level ?? 1;

        if (!$roleName) {
            return $this->failValidationErrors(['role_name' => 'role_name is required']);
        }

        $roleModel = new RoleModel();

        if ($roleModel->createRole($roleName, $criticalityLevel)) {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Role created successfully.',
                'data'    => [
                    'role_name'         => $roleName,
                    'criticality_level' => $criticalityLevel
                ]
            ]);
        } else {
            return $this->fail('Role creation failed. Role may already exist.', 400);
        }
    }

    // GET /api/v1/admin/permissions
    // List all permissions
    public function listPermissions()
    {
        $permissionModel = new PermissionModel();
        $permissions = $permissionModel->findAll();

        return $this->respond([
            'status' => 200,
            'data'   => $permissions
        ]);
    }

    // POST /api/v1/admin/permissions/create
    // Create a new permission
    public function createPermission()
    {
        $data = $this->request->getJSON();

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $permissionKey = $data->permission_key ?? null;
        $description = $data->description ?? '';

        if (!$permissionKey) {
            return $this->failValidationErrors(['permission_key' => 'permission_key is required']);
        }

        $permissionModel = new PermissionModel();

        if ($permissionModel->createPermission($permissionKey, $description)) {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Permission created successfully.',
                'data'    => [
                    'permission_key' => $permissionKey,
                    'description'    => $description
                ]
            ]);
        } else {
            return $this->fail('Permission creation failed. Permission may already exist.', 400);
        }
    }

    // DELETE /api/v1/admin/permissions/{permissionId}
    // Delete a permission
    public function deletePermission($permissionId)
    {
        $permissionModel = new PermissionModel();

        if ($permissionModel->deletePermission($permissionId)) {
            return $this->respond([
                'status'  => 200,
                'message' => 'Permission deleted successfully.'
            ]);
        } else {
            return $this->fail('Permission deletion failed. Permission may not exist.', 400);
        }
    }

    // GET /api/v1/admin/users
    // List all users
    public function listUsers()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        return $this->respond([
            'status' => 200,
            'data'   => $users
        ]);
    }
}
