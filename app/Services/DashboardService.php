<?php

namespace App\Services;

class DashboardService
{
    public static function resolve(string $activeRole): array
    {
        return match ($activeRole) {
            'admin', 'payroll_admin' => [
                'view' => 'admin/dashboard',
                'title' => 'Admin Dashboard'
            ],
            'employee' => [
                'view' => 'employee/dashboard',
                'title' => 'Employee Dashboard'
            ],
            'manager' => [
                'view' => 'manager/dashboard',
                'title' => 'Manager Dashboard'
            ],
            default => [
                'view' => 'dashboard',
                'title' => 'Dashboard'
            ],
        };
    }
}
