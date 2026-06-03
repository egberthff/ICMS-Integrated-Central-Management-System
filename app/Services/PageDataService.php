<?php

namespace App\Services;

use App\Models\UserRoleModel;
use App\Models\RoleModel;
use App\Models\UserModel;

/**
 * PageDataService - Handles data fetching for all pages
 * 
 * Each page can have a data handler that fetches necessary data
 * This keeps the controller clean and data handling centralized
 */
class PageDataService
{
    /**
     * Call a data handler method by name
     * 
     * @param string|null $handlerName Handler method name
     * @param string $userId Current user ID
     * @param string $activeRole Current active role
     * @return array Data returned by handler
     */
    public static function fetchPageData(?string $handlerName, string $userId, string $activeRole): array
    {
        // If no handler specified, return empty data
        if (!$handlerName) {
            return [];
        }

        // Check if method exists and is callable
        if (!method_exists(self::class, $handlerName)) {
            return [];
        }

        try {
            return call_user_func([self::class, $handlerName], $userId, $activeRole);
        } catch (\Exception $e) {
            log_message('error', "Error in PageDataService::{$handlerName}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch users data
     */
    public static function fetchUsersData(string $userId, string $activeRole): array
    {
        $user = new UserModel();
        return [
            'users' => $user->getUsersWithRoles(),
            'totalUsers' => count($user->getUsersWithRoles()),
        ];
    }

    /**
     * Fetch roles data
     */
    /**
     * Retrieve role data and flattened permission keys dynamically.
     *
     * @param string|null $userId Optional specific role UUID
     * @return array|null Transformed dataset array, or null if specific role is missing
     */
    public static function fetchRolesData(string $userId, string $activeRole): array
    {
        $role = new RoleModel();
        $rawResult = $role->getRolesWithPermissions(null);
        if ($rawResult === null) {
            return []; // Single role request failed to find record
        }
        // log_message('error', print_r($rawResult, true));
        // Case A: Transform a single role payload structure
        // if (isset($roleId) && $roleId !== null) {
        //     log_message('error', "Data Service line 76");
        //     return [
        //         'role_id' => $rawResult['role_id'],
        //         'role_name' => $rawResult['role_name'],
        //         'criticality_level' => $rawResult['criticality_level'],
        //         'permissions' => array_column($rawResult['permissions'], 'permission_key')
        //     ];
        // }

        // Case B: Transform mass collection array output
        $formattedCollection = [];
        foreach ($rawResult as $rolePayloads => $rolePayload) {
            $formattedCollection[] = [
                'role_id' => $rolePayload['role_id'],
                'role_name' => $rolePayload['role_name'],
                'criticality_level' => $rolePayload['criticality_level'],
                'permissions' => array_column($rolePayload['permissions'], 'permission_key')
            ];
        }
        $data['roles'] = $formattedCollection;
        return $data;
    }

    /**
     * Fetch permissions data
     */
    public static function fetchPermissionsData(string $userId, string $activeRole): array
    {
        // Implement actual permissions data fetching here
        return [
            'permissions' => [],
            'totalPermissions' => 0,
        ];
    }

    /**
     * Fetch user roles data for switch role page
     */
    public static function fetchUserRolesData(string $userId, string $activeRole): array
    {
        $userRoleModel = new UserRoleModel();
        $userRoles = $userRoleModel->getUserRoles($userId);

        return [
            'userRoles' => $userRoles,
            'activeRole' => $activeRole,
            'totalRoles' => count($userRoles),
        ];
    }

    // Add more data handlers here as needed
    // public static function fetchReportsData(...) { ... }
    // public static function fetchSettingsData(...) { ... }
}
