<?php

namespace App\Services;

/**
 * PageConfigService - Centralized configuration for all pages/menus
 * 
 * Maps page names to their:
 * - View path
 * - Required roles
 * - Data handler method
 * - Title and description
 * 
 * This allows a single dynamic handler method to manage all pages
 */
class PageConfigService
{
    /**
     * Get page configuration by key
     * 
     * @param string $pageKey Unique identifier for the page (e.g., 'users', 'roles', 'dashboard')
     * @return array|null Configuration array or null if not found
     */
    public static function getPageConfig(string $pageKey): ?array
    {
        $pages = self::getPageConfigs();
        return $pages[$pageKey] ?? null;
    }

    /**
     * Get all page configurations
     * 
     * @return array Array of all page configurations
     */
    public static function getPageConfigs(): array
    {
        return [
            // Dashboard pages
            'dashboard' => [
                'title' => 'Dashboard',
                'view' => 'payroll/dashboard', // Dynamic based on DashboardService
                'icon' => 'bi bi-house-door',
                'requiredRoles' => [], // Empty = all roles
                'dataHandler' => null, // No additional data needed
                'routeUrl' => '/dashboard',
            ],

            //Adding new employee record
            'add-new-employee' => [
                'title' => 'Add New Employee',
                'view' => 'human_resource/add_employee',
                'icon' => 'bi bi-user',
                'requiredRoles' => ['hr', 'admin', 'admin_manage', 'admin_manager'],
                'dataHandler' => null,
                'routeUrl' => '/human-resource/add-new-employee'
            ],

            // Employee announcements (future feature)
            'announcements' => [
                'title' => 'Announcements',
                'view' => 'employee/announcements',
                'icon' => 'bi bi-bell',
                'requiredRoles' => ['employee', 'payroll_employee'],
                'dataHandler' => null,
                'routeUrl' => '/employee/announcements',
            ],

            // Admin pages - Users
            'users' => [
                'title' => 'Users Management',
                'view' => 'admin/users',
                'icon' => 'bi bi-people',
                'requiredRoles' => ['admin', 'admin_manage'],
                'dataHandler' => 'fetchUsersData',
                'routeUrl' => '/admin/users',
            ],

            // Admin pages - Roles
            'roles' => [
                'title' => 'Roles Management',
                'view' => 'admin/roles',
                'icon' => 'bi bi-shield-check',
                'requiredRoles' => ['admin', 'admin_manage'],
                'dataHandler' => 'fetchRolesData',
                'routeUrl' => '/admin/roles',
            ],

            // Admin pages - Permissions
            'permissions' => [
                'title' => 'Permissions Management',
                'view' => 'admin/permissions',
                'icon' => 'bi bi-lock',
                'requiredRoles' => ['admin', 'admin_manage'],
                'dataHandler' => 'fetchPermissionsData',
                'routeUrl' => '/admin/permissions',
            ],

            // Employee timesheet
            'timesheet' => [
                'title' => 'Timesheet',
                'view' => 'employee/timesheet',
                'icon' => 'bi bi-clock',
                'requiredRoles' => ['employee', 'payroll_admin'],
                'dataHandler' => null, // No additional data needed for basic view
                'routeUrl' => 'timesheet',
            ],

            // Employee payslip
            'payslip' => [
                'title' => 'My Payslip',
                'view' => 'employee/payslip',
                'icon' => 'bi bi-file-earmark-text',
                'requiredRoles' => ['employee'],
                'dataHandler' => null, // Payslip data loaded via AJAX in the view
                'routeUrl' => '/payslip',
            ],

            // Role management
            'switch-role' => [
                'title' => 'Switch Role',
                'view' => 'switch_role',
                'icon' => 'bi bi-toggle-on',
                'requiredRoles' => [], // All roles can see this if they have > 1 role
                'dataHandler' => 'fetchUserRolesData',
                'routeUrl' => '/switch-role',
                'requireMultipleRoles' => true, // Only show if user has > 1 role
            ],
            // Add more pages here as needed:
            // 'profile' => [...]
            // 'reports' => [...]
            // 'settings' => [...]
        ];
    }

    /**
     * Check if a role has access to a page
     * 
     * @param string $pageKey Page identifier
     * @param string $userRole User's active role
     * @return bool True if user has access
     */
    public static function canAccessPage(string $pageKey, string $userRole): bool
    {
        $config = self::getPageConfig($pageKey);
        if (!$config) {
            return false;
        }

        // If no required roles specified, all roles can access
        if (empty($config['requiredRoles'])) {
            return true;
        }

        // Check if user's role is in required roles
        return in_array($userRole, $config['requiredRoles'], true);
    }

    /**
     * Get page key by route URL
     * 
     * @param string $routeUrl Route URL (e.g., '/admin/users')
     * @return string|null Page key or null if not found
     */
    public static function getPageKeyByUrl(string $routeUrl): ?string
    {
        $pages = self::getPageConfigs();
        foreach ($pages as $key => $config) {
            if ($config['routeUrl'] === $routeUrl) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Get all pages accessible to a specific role
     * 
     * @param string $activeRole User's active role
     * @param int $totalRoles Total number of roles user has
     * @return array Array of accessible pages [key => config]
     */
    public static function getAccessiblePages(string $activeRole, int $totalRoles = 1): array
    {
        $pages = self::getPageConfigs();
        $accessible = [];

        foreach ($pages as $key => $config) {
            // Check role-based access
            if (!empty($config['requiredRoles'])) {
                if (!in_array($activeRole, $config['requiredRoles'], true)) {
                    continue; // Skip if user doesn't have required role
                }
            }

            // Check if page requires multiple roles
            if (isset($config['requireMultipleRoles']) && $config['requireMultipleRoles']) {
                if ($totalRoles <= 1) {
                    continue; // Skip if user doesn't have multiple roles
                }
            }

            $accessible[$key] = $config;
        }

        return $accessible;
    }
}
