<?php

namespace App\Services;

/**
 * MenuService - Dynamically returns menu items based on user role
 * 
 * Ensures:
 * - Dashboard always visible
 * - Switch Role only shows if user has multiple roles
 * - Admin menus (Users, Roles, Permissions) only for admin/payroll_admin roles
 * - Logout always visible
 */
class MenuService
{
     /**
      * Get menu items for a specific role
      * 
      * @param string $activeRole Current active role
      * @param int $totalRoles Total number of roles the user has
      * @return array Array of menu items with label, icon, and url
      */
    public static function getMenus(string $activeRole, int $totalRoles = 1): array
    {
        $menus = [];

        // 1. Dashboard - Always visible to all roles
        $menus[] = [
            'label' => 'Dashboard',
            'icon' => 'bi bi-house-door',
            'url' => '/dashboard',
        ];

        // 2. Admin-only menus (Users, Roles, Permissions)
        // Only visible to admin and payroll_admin roles
        if (self::isAdminRole($activeRole)) {
            $menus[] = [
                'label' => 'Users',
                'icon' => 'bi bi-people',
                'url' => '/admin/users',
            ];

            $menus[] = [
                'label' => 'Roles',
                'icon' => 'bi bi-shield-check',
                'url' => '/admin/roles',
            ];

            $menus[] = [
                'label' => 'Permissions',
                'icon' => 'bi bi-lock',
                'url' => '/admin/permissions',
            ];
        }

        if (self::isEmployeeRole($activeRole)){
            $menus[] = [
                'label' => 'My Payroll',
                'icon' => 'bi bi-wallet2',
                'url' => '/employee/payroll',
            ];
            $menus[] = [
                'label' => 'Timesheet',
                'icon' => 'bi bi-clock',
                'url' => '/employee/timesheet',
            ];
            $menus[] = [
                'label' => 'Announcements',
                'icon' => 'bi bi-bell',
                'url' => '/employee/announcements',
            ];
        }

        // 3. Switch Role - Only visible if user has multiple roles
        if ($totalRoles > 1) {
            // Add separator before switch role
            $menus[] = [
                'type' => 'separator',
            ];

            $menus[] = [
                'label' => 'Switch Role',
                'icon' => 'bi bi-lock',
                'url' => '/switch-role',
            ];
        }

        // 4. Logout - Always visible at the bottom
        // Add separator if not already added
        if ($totalRoles <= 1) {
            $menus[] = [
                'type' => 'separator',
            ];
        }

        $menus[] = [
            'label' => 'Logout',
            'icon' => 'bi bi-box-arrow-right',
            'url' => '#',
            'onclick' => 'logout()',
        ];

        return $menus;
    }

    /**
      * Check if a role has admin privileges
      * 
      * @param string $role Role name
      * @return bool True if role is admin or payroll_admin
      */
    private static function isAdminRole(string $role): bool
    {
        return in_array($role, ['admin', 'payroll_admin', 'admin_manage'], true);
    }

    private static function isEmployeeRole(string $role): bool
    {
        return in_array($role, ['employee', 'payroll_employee'], true);
    }


}
