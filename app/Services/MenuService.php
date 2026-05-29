<?php

namespace App\Services;

/**
 * MenuService - Builds sidebar menus from configuration
 *
 * DRY goals:
 * - Avoid hardcoding menu items and role checks in PHP.
 * - Render the same menu structure from PageConfigService.
 * - Keep only the Logout action here.
 */
class MenuService
{
    /**
     * @param string $activeRole
     * @param int $totalRoles
     * @return array<int, array<string, mixed>>
     */
    public static function getMenus(string $activeRole, int $totalRoles = 1): array
    {
        $menus = [];

        $pageConfigs = PageConfigService::getPageConfigs();

        // Keep Dashboard first
        if (isset($pageConfigs['dashboard']) && self::isPageAccessible($pageConfigs['dashboard'], $activeRole, $totalRoles)) {
            $menus[] = self::toMenuItem($pageConfigs['dashboard']);
        }

        // Add the rest of pages in config order (except dashboard)
        foreach ($pageConfigs as $pageKey => $config) {
            if ($pageKey === 'dashboard') {
                continue;
            }

            if (!self::isPageAccessible($config, $activeRole, $totalRoles)) {
                continue;
            }

            // Switch Role: insert separator only when it will be shown
            if ($pageKey === 'switch-role') {
                if ($totalRoles > 1) {
                    $menus[] = ['type' => 'separator'];
                    $menus[] = self::toMenuItem($config);
                }
                continue;
            }

            // All other pages
            $menus[] = self::toMenuItem($config);
        }

        // Separator before Logout if Switch Role didn't already add one
        $hasSwitchRole = isset($pageConfigs['switch-role'])
            && $totalRoles > 1
            && self::isPageAccessible($pageConfigs['switch-role'], $activeRole, $totalRoles);

        if (!$hasSwitchRole) {
            $menus[] = ['type' => 'separator'];
        }

        $menus[] = [
            'label' => 'Logout',
            'icon' => 'bi bi-box-arrow-right',
            'url' => '#',
            'onclick' => 'logout()',
        ];

        return $menus;
    }

    private static function isPageAccessible(array $config, string $activeRole, int $totalRoles): bool
    {
        if (!$config) {
            return false;
        }

        // Role access
        if (!empty($config['requiredRoles'])) {
            if (!in_array($activeRole, $config['requiredRoles'], true)) {
                return false;
            }
        }

        // Multi-role requirement
        if (!empty($config['requireMultipleRoles']) && $totalRoles <= 1) {
            return false;
        }

        return true;
    }

    private static function toMenuItem(array $pageConfig): array
    {
        return [
            'label' => $pageConfig['title'] ?? '',
            'icon' => $pageConfig['icon'] ?? '',
            'url' => $pageConfig['routeUrl'] ?? '#',
        ];
    }
}

