<?php

namespace App\Services;

class DashboardService
{
    public static function resolve(string $activeRole): array
    {
        return match ($activeRole) {
            'payroll', 'payroll_admin' => [
                'view' => 'payroll/dashboard',
                'title' => 'Payroll Dashboard'
            ],
            'employee' => [
                'view' => 'employee/dashboard',
                'title' => 'Employee Dashboard'
            ],
            'Admin' => [
                'view' => 'administrator/dashboard',
                'title' => 'Manager Dashboard'
            ],
            default => [
                'view' => 'dashboard',
                'title' => 'Dashboard'
            ],
        };
    }
}
