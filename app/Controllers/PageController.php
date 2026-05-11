<?php

namespace App\Controllers;
use Config\Services;
use App\Models\UserRoleModel;
use App\Models\RoleModel;
use App\Services\MenuService;
use App\Services\PageConfigService;
use App\Services\PageDataService;

class PageController extends BaseController
{
    /**
     * Get authenticated user data (role, id, etc.)
     * Returns null if auth fails
     */
    private function getAuthenticatedUser(): ?array
    {
        $token = $this->request->getCookie('authToken');
        if (!$token) {
            return null;
        }

        try {
            $decodedToken = Services::jwtDecoder($token);
            $userId = $decodedToken->sub ?? null;
            $activeRole = $decodedToken->active_role ?? 'employee';

            // Count total roles for this user
            $userRoleModel = new UserRoleModel();
            $totalRoles = count($userRoleModel->getUserRoles($userId));

            return [
                'userId' => $userId,
                'activeRole' => $activeRole,
                'totalRoles' => $totalRoles,
            ];
        } catch (\Exception $e) {
            log_message('error', 'Auth error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get menu items based on current user's roles
     * Decodes JWT to extract active role and counts total user roles
     */
    private function getMenuData(): array
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return ['menus' => []];
        }

        // Get menu configuration based on role and role count
        $menus = MenuService::getMenus($user['activeRole'], $user['totalRoles']);

        return [
            'menus' => $menus,
            'activeRole' => $user['activeRole'],
            'totalRoles' => $user['totalRoles'],
            'userId' => $user['userId'],
        ];
    }

    /**
     * Handle any page dynamically based on page configuration
     * This is the core method that all pages delegate to
     * 
     * @param string $pageKey Page identifier (e.g., 'users', 'roles', 'dashboard')
     * @return mixed View response or redirect
     */
    private function handlePage(string $pageKey)
    {
        // Require authentication
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return redirect()->to('/');
        }

        // Get page configuration
        $pageConfig = PageConfigService::getPageConfig($pageKey);
        if (!$pageConfig) {
            return redirect()->to('/dashboard')->with('error', 'Page not found');
        }

        // Check access permissions
        if (!PageConfigService::canAccessPage($pageKey, $user['activeRole'])) {
            // Special case for pages that require multiple roles
            if (isset($pageConfig['requireMultipleRoles']) && $pageConfig['requireMultipleRoles']) {
                if ($user['totalRoles'] <= 1) {
                    return redirect()->to('/dashboard')->with('error', 'You must have multiple roles to access this page');
                }
            } else {
                return redirect()->to('/dashboard')->with('error', 'You do not have access to this page');
            }
        }

        // Fetch page-specific data
        $pageData = PageDataService::fetchPageData(
            $pageConfig['dataHandler'],
            $user['userId'],
            $user['activeRole']
        );

        // Determine view path (special handling for dashboard)
        $view = $pageConfig['view'];
        if ($pageKey === 'dashboard') {
            $dashboardConfig = \App\Services\DashboardService::resolve($user['activeRole']);
            $view = $dashboardConfig['view'];
        }

        // Merge menu data with page data
        $menuData = $this->getMenuData();
        $viewData = array_merge([
            'title' => $pageConfig['title'],
        ], $pageData, $menuData);

        return view($view, $viewData);
    }

    // private function requireAuth()
    // {
    //     $token = $this->request->getCookie('authToken');
    //     if (!$token) {
    //         return redirect()->to('/');
    //     }

    //     try {
    //         Services::jwtDecoder($token);
    //     } catch (\Exception $e) {
    //         setcookie('authToken', '', time() - 3600, '/');
    //         // return redirect()->to('/');
    //     }
    // }

    // public function login()
    // {
    //     $token = $this->request->getCookie('authToken');
    //     if ($token !== null) {
    //         // Validate the token before redirecting
    //         try {
    //             Services::jwtDecoder($token);
    //             return redirect()->to('/dashboard');
    //         } catch (\Exception $e) {
    //             // Invalid token, clear the cookie
    //             setcookie('authToken', '', time() - 3600, '/');
    //         }
    //     }
    //      return view('login');
    // }
    public function login() {
    $token = $this->request->getCookie('authToken');
    
    if ($token !== null) {
        try {
            // Validate JWT and check expiration
            $payload = Services::jwtDecoder($token);
            
            // Optional: Check for required roles/claims
            if (!isset($payload['role']) || $payload['role'] !== 'user') {
                throw new \Exception('Invalid token claims');
            }
            
            // Set secure session data if needed
            session()->set(['user_id' => $payload['sub']]);
            
            return redirect()->to('/dashboard');
        } catch (\Exception $e) {
            // Log the invalid token attempt
            log_message('warning', 'Invalid JWT login attempt: ' . $e->getMessage());
            
            // Clear the invalid cookie
            setcookie(
                'authToken', 
                '', 
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true, // Only send over HTTPS
                    'httponly' => true, // Prevent JavaScript access
                    'samesite' => 'Strict'
                ]
            );
        }
    }
    
    return view('login');
}

    /**
     * Dashboard - Route to dynamic handler
     */
    public function dashboard()
    {
        return $this->handlePage('dashboard');
    }

    /**
     * Users Management - Route to dynamic handler
     */
    public function users()
    {
        return $this->handlePage('users');
    }

    /**
     * Roles Management - Route to dynamic handler
     */
    public function roles()
    {
        return $this->handlePage('roles');
    }

    /**
     * Permissions Management - Route to dynamic handler
     */
    public function permissions()
    {
        return $this->handlePage('permissions');
    }

    /**
     * Switch Role - Route to dynamic handler
     */
    public function switchRole()
    {
        return $this->handlePage('switch-role');
    }
}
