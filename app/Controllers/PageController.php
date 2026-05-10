<?php

namespace App\Controllers;
use Config\Services;

class PageController extends BaseController
{
    private function requireAuth()
    {
        $token = $this->request->getCookie('authToken');
        if (!$token) {
            return redirect()->to('/');
        }

        try {
            Services::jwtDecoder($token);
        } catch (\Exception $e) {
            setcookie('authToken', '', time() - 3600, '/');
            return redirect()->to('/');
        }
    }

    public function login()
    {
        $token = $this->request->getCookie('authToken');
        if ($token) {
            // Validate the token before redirecting
            try {
                Services::jwtDecoder($token);
                return redirect()->to('/dashboard');
            } catch (\Exception $e) {
                // Invalid token, clear the cookie
                setcookie('authToken', '', time() - 3600, '/');
            }
        }
        return view('login');
    }

    public function dashboard()
    {
        $token = $this->request->getCookie('authToken');
        if (!$token) {
            return redirect()->to('/');
        }

        try {
            $decodedToken = Services::jwtDecoder($token);
            $activeRole = $decodedToken->active_role ?? 'employee';
        } catch (\Exception $e) {
            setcookie('authToken', '', time() - 3600, '/');
            return redirect()->to('/');
        }

        $dashboardConfig = \App\Services\DashboardService::resolve($activeRole);
 log_message('error', 'Resolve dashboard congif path ' . print_r($dashboardConfig, true));
        return view($dashboardConfig['view'], [
            'title' => $dashboardConfig['title'],
            'activeRole' => $activeRole
        ]);
    }

    public function users()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }

        return view('admin/users', [
            'title' => 'Users Management'
        ]);
    }

    public function roles()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }

        return view('admin/roles', [
            'title' => 'Roles Management'
        ]);
    }

    public function permissions()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }

        return view('admin/permissions', [
            'title' => 'Permissions Management'
        ]);
    }

    public function switchRole()
    {
        return view('switch_role', [
            'title' => 'Switch Role'
        ]);
    }
}
