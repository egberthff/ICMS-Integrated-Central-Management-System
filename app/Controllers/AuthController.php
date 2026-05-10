<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\RolePermissionModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class AuthController extends ResourceController
{
    // /login
    public function login()
    {
        // log_message('error', 'Login Payload: ' . print_r($this->request->getBody(), true)); // Debug log for incoming payload
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        if (empty($username) || empty($password)) {
            return $this->failValidationErrors([
                'username' => 'Username is required',
                'password' => 'Password is required'
            ]);
        }

        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();
        $roleModel = new RoleModel();
        $rolePermissionModel = new RolePermissionModel();

        $user = $userModel->where('username', $username)
            ->where('is_active', 1)
            ->first();

        if (empty($user) || !password_verify($password, $user['password_hash'] ?? '')) {
            return $this->failUnauthorized('Invalid username or password.');
        }

        $availableRoles = $userRoleModel->getUserRoles((string) $user['user_id']);
        $activeRole = $availableRoles[0]['role_name'] ?? 'employee';
        $activeRoleRecord = $roleModel->getRoleByName($activeRole);
        $permissions = [];

        if ($activeRoleRecord) {
            $permissions = array_column(
                $rolePermissionModel->getRolePermissions($activeRoleRecord['role_id']),
                'permission_key'
            );
        }

        $tokenData = [
            'sub'             => $user['user_id'],
            'username'        => $user['username'],
            'active_role'     => $activeRole,
            'is_critical'     => in_array($activeRole, ['payroll_admin', 'owner', 'accounting']),
            'permissions'     => $permissions,
            'mfa_verified_at' => null
        ];

        $jwt = Services::jwtEncoder($tokenData);

        return $this->respond([
            'status'  => 200,
            'message' => 'Login successful',
            'token'   => $jwt,
            'user'    => [
                'user_id'    => $user['user_id'],
                'username'   => $user['username'],
                'active_role'=> $activeRole,
                'permissions'=> $permissions
            ]
        ]);
    }
    // POST /api/auth/switch-role
    public function switchRole()
    {
        $data = $this->request->getJSON();
        
        if (!$data) {
            return $this->failValidationErrors(['message' => 'Invalid JSON payload']);
        }

        $authHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        if (!$authHeader) {
            return $this->failUnauthorized('Authorization token is required.');
        }

        $token = str_replace('Bearer ', '', $authHeader);
        try {
            $decodedToken = Services::jwtDecoder($token);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Invalid session token.');
        }
        $userId = $decodedToken->sub ?? null;
        $targetRole = $data->target_role ?? null;
        $mfaToken = $data->mfa_token ?? null; // Optional unless critical

        if (!$userId || !$targetRole) {
            return $this->failValidationErrors([
                'user_id' => 'Authenticated user_id is required',
                'target_role' => 'target_role is required'
            ]);
        }

        $userRoleModel = new UserRoleModel();
        $roleModel = new RoleModel();
        $rolePermissionModel = new RolePermissionModel();

        // 1. Verify requested role exists
        $roleRecord = $roleModel->getRoleByName($targetRole);
        if (!$roleRecord) {
            return $this->failNotFound('Target role does not exist.');
        }

        // 2. Verify authenticated user actually owns the requested role
        if (!$userRoleModel->userHasRole($userId, $roleRecord['role_id'])) {
            return $this->failUnauthorized('You do not have access to this role.');
        }

        // 3. Check role criticality (Simulated database check)
        $isCritical = in_array($targetRole, ['payroll_admin', 'owner', 'accounting', 'admin_manage']);

        // 3. Force Step-Up MFA for critical roles
        if ($isCritical) {
            if (empty($mfaToken) || !$this->verifyMFA($userId, $mfaToken)) {
                return $this->fail([
                    'status'  => 403,
                    'error'   => 'MFA_REQUIRED',
                    'message' => 'Step-up authentication required for ' . $targetRole
                ], 403);
            }
        }

        // 4. Fetch permissions tied strictly to this single active role
        $permissions = array_column(
            $rolePermissionModel->getRolePermissions($roleRecord['role_id']),
            'permission_key'
        );

        // 5. Generate fresh dynamic session token payload
        $tokenData = [
            'sub'             => $userId,
            'active_role'     => $targetRole,
            'is_critical'     => $isCritical,
            'permissions'     => $permissions,
            'mfa_verified_at' => $isCritical ? date('Y-m-d H:i:s') : null
        ];

        // encoded dynamic token sent back to frontend/cookie
        $jwt = Services::jwtEncoder($tokenData); 

        return $this->respond([
            'status'  => 200,
            'message' => 'Switched to ' . $targetRole . ' mode successfully.',
            'token'   => $jwt
        ]);
    }

    private function verifyMFA($userId, $token)
    {
        // Integration logic with Google Authenticator, Duo, or SMS TOTP goes here
        return $token === "123456"; // Demonstration placeholder
    }
}
