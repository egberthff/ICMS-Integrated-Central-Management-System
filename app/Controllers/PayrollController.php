<?php

namespace App\Controllers;

use App\Models\PayslipModel;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\TimesheetModel;

class PayrollController extends BaseApiController
{
    protected $payslipModel;
    protected $employeeModel;
    protected $userModel;
    protected $timesheetModel;

    public function __construct()
    {
        // parent::__construct();
        $this->payslipModel = new PayslipModel();
        $this->employeeModel = new EmployeeModel();
        $this->userModel = new UserModel();
        $this->timesheetModel = new TimesheetModel();
    }

    /**
     * Generate and view a specific payslip
     */
    public function viewPayslip($id = null)
    {
        if (!$id) {
            return $this->apiBadRequest('Payslip ID is required');
        }

        $payslip = $this->payslipModel->getPayslipById($id);

        if (!$payslip) {
            return $this->apiNotFound('Payslip not found');
        }

        // Verify the payslip belongs to the current employee
        $userId = $this->request->activeTokenContext->user_id ?? ($this->request->activeTokenContext->sub ?? null);
        if ($payslip['employee_id'] != $userId) {
            return $this->apiForbidden('Access denied to this payslip');
        }

        return $this->apiSuccess([
            'payslip' => $payslip
        ]);
    }

    /**
     * List all payslips for the current employee
     */
    public function listPayslips()
    {
        $userId = $this->request->activeTokenContext->user_id ?? ($this->request->activeTokenContext->sub ?? null);
        if (!$userId) {
            return $this->apiUnauthorized('Invalid session');
        }

        // Get query parameters for filtering and pagination
        $limit = $this->request->getGet('limit', 10);
        $offset = $this->request->getGet('offset', 0);
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $status = $this->request->getGet('status');

        $filters = [];
        if ($startDate)
            $filters['start_date'] = $startDate;
        if ($endDate)
            $filters['end_date'] = $endDate;
        if ($status)
            $filters['status'] = $status;

        $payslips = $this->payslipModel->getEmployeePayslips($userId, $limit, $offset);

        // Get total count for pagination
        $totalCount = $this->payslipModel->searchPayslips($userId, $filters);
        $total = count($totalCount);

        return $this->apiSuccess([
            'payslips' => $payslips,
            'pagination' => [
                'limit' => (int) $limit,
                'offset' => (int) $offset,
                'total' => $total
            ]
        ]);
    }

    /**
     * Get the latest payslip for the current employee
     */
    public function latestPayslip()
    {
        $userId = $this->request->activeTokenContext->user_id ?? ($this->request->activeTokenContext->sub ?? null);
        if (!$userId) {
            return $this->apiUnauthorized('Invalid session');
        }

        $payslip = $this->payslipModel->getLatestPayslip($userId);

        if (!$payslip) {
            return $this->apiNotFound('No payslip found');
        }

        return $this->apiSuccess([
            'payslip' => $payslip
        ]);
    }



    /**
     * Calculate payslip components from timesheet and employee data
     */
    public function calculatePayslip($data)
    {
        $employeeId = $data['employee_id'];
        $startDate = $data['pay_period_start'];
        $endDate = $data['pay_period_end'];

        // Get timesheet data for the period
        $timesheet = $this->timesheetModel->where([
            'user_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->first();

        // Get employee info
        $employee = $this->employeeModel->where('user_id', $employeeId)->first();

        // Default values
        $basicSalary = floatval($data['basic_salary'] ?? 0);
        $regularHours = $timesheet ? floatval($timesheet['regular_hours']) : 0;
        $otHours = $timesheet ? floatval($timesheet['ot_hours']) : 0;
        $nightDiffHours = $timesheet ? floatval($timesheet['night_diff']) : 0;
        $daysWorked = $timesheet ? floatval($timesheet['days_worked']) : 0;

        // Calculate earnings
        $hourlyRate = $basicSalary / (8 * 22); // Assuming 8 hours/day, 22 days/month
        $overtimePay = $hourlyRate * 1.25 * $otHours; // 25% premium for overtime
        $nightDiffPay = $hourlyRate * 0.10 * $nightDiffHours; // 10% premium for night diff
        $allowances = floatval($data['allowances'] ?? 0);
        $bonuses = floatval($data['bonuses'] ?? 0);

        $grossEarnings = $basicSalary + $overtimePay + $nightDiffPay + $allowances + $bonuses;

        // Calculate deductions (Philippine standard rates as example)
        $sssDeduction = $this->calculateSss($basicSalary);
        $philhealthDeduction = $this->calculatePhilhealth($basicSalary);
        $pagibigDeduction = 100.00; // Fixed Pag-IBIG contribution
        $taxDeduction = $this->calculateTax($basicSalary);
        $otherDeductions = floatval($data['other_deductions'] ?? 0);

        $totalDeductions = $sssDeduction + $philhealthDeduction + $pagibigDeduction + $taxDeduction + $otherDeductions;
        $netPay = $grossEarnings - $totalDeductions;

        // Get year-to-date values (simplified)
        $ytdEarnings = $this->getYearToDateEarnings($employeeId, $endDate);
        $ytdDeductions = $this->getYearToDateDeductions($employeeId, $endDate);
        $ytdNet = $ytdEarnings - $ytdDeductions;

        // Get leave credits (simplified)
        $vacationLeave = $timesheet ? floatval($timesheet['vac_leave']) : 0;
        $sickLeave = $timesheet ? floatval($timesheet['sick_leave']) : 0;

        return [
            'employee_id' => $employeeId,
            'pay_period_start' => $startDate,
            'pay_period_end' => $endDate,
            'date_issued' => date('Y-m-d'),
            'basic_salary' => $basicSalary,
            'overtime_pay' => $overtimePay,
            'allowances' => $allowances,
            'bonuses' => $bonuses,
            'gross_earnings' => $grossEarnings,
            'sss_deduction' => $sssDeduction,
            'philhealth_deduction' => $philhealthDeduction,
            'pagibig_deduction' => $pagibigDeduction,
            'tax_deduction' => $taxDeduction,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'payment_date' => $data['payment_date'] ?? null,
            'year_to_date_earnings' => $ytdEarnings,
            'year_to_date_deductions' => $ytdDeductions,
            'year_to_date_net' => $ytdNet,
            'leave_credits_vacation' => $vacationLeave,
            'leave_credits_sick' => $sickLeave,
            'status' => 'pending',
            'remarks' => $data['remarks'] ?? null
        ];
    }

    /**
     * Calculate SSS contribution (simplified)
     */
    private function calculateSss($salary)
    {
        // Simplified SSS calculation - in reality this uses contribution tables
        if ($salary <= 0)
            return 0;
        return min($salary * 0.045, 1800.00); // 4.5% capped
    }

    /**
     * Calculate PhilHealth contribution (simplified)
     */
    private function calculatePhilhealth($salary)
    {
        // Simplified PhilHealth calculation
        if ($salary <= 0)
            return 0;
        return min($salary * 0.03, 900.00); // 3% capped
    }

    /**
     * Calculate income tax (simplified)
     */
    private function calculateTax($salary)
    {
        // Simplified tax calculation - progressive tax table
        if ($salary <= 0)
            return 0;

        // Annual equivalent for tax calculation
        $annualSalary = $salary * 12;

        // Simplified 2023 tax brackets (annual)
        if ($annualSalary <= 250000) {
            $annualTax = 0;
        } elseif ($annualSalary <= 400000) {
            $annualTax = ($annualSalary - 250000) * 0.15;
        } elseif ($annualSalary <= 800000) {
            $annualTax = 22500 + ($annualSalary - 400000) * 0.20;
        } elseif ($annualSalary <= 2000000) {
            $annualTax = 102500 + ($annualSalary - 800000) * 0.25;
        } elseif ($annualSalary <= 8000000) {
            $annualTax = 402500 + ($annualSalary - 2000000) * 0.30;
        } else {
            $annualTax = 2202500 + ($annualSalary - 8000000) * 0.35;
        }

        return $annualTax / 12; // Monthly tax
    }

    /**
     * Get year-to-date earnings for employee
     */
    private function getYearToDateEarnings($employeeId, $endDate)
    {
        // Simplified - in reality would sum all payslips for the year
        $year = substr($endDate, 0, 4);
        $startOfYear = $year . '-01-01';

        $payslips = $this->payslipModel->where([
            'employee_id' => $employeeId,
            'pay_period_start >= ' => $startOfYear,
            'pay_period_end <= ' => $endDate
        ])->findAll();

        $total = 0;
        foreach ($payslips as $payslip) {
            $total += floatval($payslip['gross_earnings']);
        }

        return $total;
    }

    /**
     * Get year-to-date deductions for employee
     */
    private function getYearToDateDeductions($employeeId, $endDate)
    {
        // Simplified - in reality would sum all deductions for the year
        $year = substr($endDate, 0, 4);
        $startOfYear = $year . '-01-01';

        $payslips = $this->payslipModel->where([
            'employee_id' => $employeeId,
            'pay_period_start >= ' => $startOfYear,
            'pay_period_end <= ' => $endDate
        ])->findAll();

        $total = 0;
        foreach ($payslips as $payslip) {
            $total += floatval($payslip['total_deductions']);
        }

        return $total;
    }

    // Existing methods
    public function disburse()
    {
        return $this->apiSuccess(['message' => 'Payroll API is operational']);
    }

    public function summary()
    {
        return $this->apiSuccess(['message' => 'Payroll summary endpoint is operational']);
    }
}