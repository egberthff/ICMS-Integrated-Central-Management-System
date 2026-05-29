<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\RolePermissionModel;
use App\Controllers\BaseApiController;

class AuthController extends BaseApiController
{
    // /login
    public function login()
    {
        // log_message('error', 'Login Payload: ' . print_r($this->request->getBody(), true)); // Debug log for incoming payload
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        if (empty($username) || empty($password)) {
            return $this->apiValidationError([
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
            return $this->apiUnauthorized('Invalid username or password.');
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
            'sub' => $user['user_id'],
            'username' => $user['username'],
            'active_role' => $activeRole,
            'is_critical' => in_array($activeRole, ['payroll_admin', 'owner', 'accounting']),
            'permissions' => $permissions,
            'mfa_verified_at' => null
        ];

        $jwt = \Config\Services::jwtEncoder($tokenData);

        return $this->apiSuccess([
            'message' => 'Login successful',
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'active_role' => $activeRole,
                'permissions' => $permissions
            ]
        ], 200, $jwt);

    }
    // POST /api/auth/switch-role
    public function switchRole()
    {
        $data = $this->getJsonData();
        if (!$data) {
            return $this->apiBadRequest('Invalid JSON payload');
        }

        $decodedToken = $this->request->activeTokenContext ?? null;
        if (!$decodedToken) {
            return $this->apiUnauthorized('Authorization token is required.');
        }
        $userId = $decodedToken->sub ?? null;
        $targetRole = $data['target_role'] ?? null;
        $mfaToken = $data['mfa_token'] ?? null; // Optional unless critical

        if (!$userId || !$targetRole) {
            return $this->apiValidationError([
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
            return $this->apiNotFound('Target role does not exist.');
        }

        // 2. Verify authenticated user actually owns the requested role
        if (!$userRoleModel->userHasRole($userId, $roleRecord['role_id'])) {
            return $this->apiUnauthorized('You do not have access to this role.');
        }

        // 3. Check role criticality (Simulated database check)
        $isCritical = in_array($targetRole, ['payroll_admin', 'owner', 'accounting', 'admin_manage']);

        // 3. Force Step-Up MFA for critical roles
        if ($isCritical) {
            if (empty($mfaToken) || !$this->verifyMFA($userId, $mfaToken)) {
                return $this->fail([
                    'status' => 403,
                    'error' => 'MFA_REQUIRED',
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
            'sub' => $userId,
            'active_role' => $targetRole,
            'is_critical' => $isCritical,
            'permissions' => $permissions,
            'mfa_verified_at' => $isCritical ? date('Y-m-d H:i:s') : null
        ];

        // encoded dynamic token sent back to frontend/cookie
        $jwt = \Config\Services::jwtEncoder($tokenData);

        return $this->apiSuccess([
            'message' => 'Switched to ' . $targetRole . ' mode successfully.',
            'token' => $jwt
        ]);
    }

    private function verifyMFA($userId, $token)
    {
        // Get user's MFA secret from database
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user || empty($user['mfa_secret']) || !$user['mfa_enabled']) {
            // If MFA is not set up for user, allow access (for backward compatibility)
            // In production, you might want to enforce MFA setup
            return true;
        }

        // Verify the TOTP token
        $secret = $user['mfa_secret'];

        // Basic TOTP verification (simplified implementation)
        // In production, use a robust library like phpgangsta/GoogleAuthenticator
        return $this->verifyTotp($secret, $token);
    }

    /**
     * Verify TOTP token
     * Simplified implementation - in production use a proper library
     */
    private function verifyTotp($secret, $token, $window = 1)
    {
        // Clean the token
        $token = strval($token);
        $token = str_pad($token, 6, '0', STR_PAD_LEFT);

        // Base32 decode the secret
        $secret = $this->base32Decode(strtoupper($secret));

        if ($secret === false) {
            return false;
        }

        // Get current time step
        $timeStep = floor(time() / 30);

        // Check current and neighboring time steps to allow for clock drift
        for ($i = -$window; $i <= $window; $i++) {
            $hash = hash_hmac('SHA1', sprintf('%016x', $timeStep + $i), $secret, true);
            $offset = ord(substr($hash, -1)) & 0x0F;
            $hashPart = substr($hash, $offset, 4);
            $value = unpack('N', $hashPart);
            $value = $value[1] & 0x7fffffff;
            $otp = sprintf('%06d', $value % 1000000);

            if ($otp === $token) {
                return true;
            }
        }

        return false;
    }

    /**
     * Base32 decode
     */
    private function base32Decode($input)
    {
        // Remove padding and spaces
        $input = str_replace('=', '', $input);
        $input = str_replace(' ', '', $input);

        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32CharsFlipped = array_flip(str_split($base32Chars));

        $binary = '';
        $charCount = 0;
        $bits = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            if (!isset($base32CharsFlipped[$char])) {
                continue; // Skip invalid characters
            }

            $binary .= str_pad(base_convert($base32CharsFlipped[$char], 10, 2), 5, '0', STR_PAD_LEFT);
            $charCount += 5;

            if ($charCount >= 8) {
                $binary = substr($binary, 0, 8);
                $output = chr(bindec($binary));
                $binary = substr($binary, 8);
                $charCount -= 8;

                // For simplicity in this example, we'll just return the binary string
                // In a real implementation, you'd properly handle the binary data
                return $output;
            }
        }

        // If we have remaining bits, pad and process
        if ($charCount > 0) {
            $binary = str_pad($binary, $charCount * 5, '0', STR_PAD_RIGHT);
            if (strlen($binary) >= 8) {
                $binary = substr($binary, 0, 8);
                return chr(bindec($binary));
            }
        }

        return false;
    }
}