<?php

namespace App\Services;

use App\Models\UserRoleModel;
use App\Models\RoleModel;

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
        // Implement actual user data fetching here
        // This would typically query the database and filter based on role
        return [
            'users' => [],
            'totalUsers' => 0,
        ];
    }

    /**
     * Fetch roles data
     */
    public static function fetchRolesData(string $userId, string $activeRole): array
    {
        $roleModel = new RoleModel();
        return [
            'roles' => $roleModel->findAll(),
            'totalRoles' => count($roleModel->findAll()),
        ];
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
