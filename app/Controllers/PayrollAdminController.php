<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\PayslipModel;
use App\Models\TimesheetModel;

class PayrollAdminController extends BaseApiController
{
    /**
     * Payroll admins prerogative: controller is called PayrollAdminController
     * (kept name for clarity).
     */

    protected PayslipModel $payslipModel;
    protected EmployeeModel $employeeModel;
    protected TimesheetModel $timesheetModel;

    public function __construct()
    {
        $this->payslipModel = new PayslipModel();
        $this->employeeModel = new EmployeeModel();
        $this->timesheetModel = new TimesheetModel();
    }

    /**
     * Calculate payslip for an employee for a pay period based on timesheets,
     * then insert/update the payslip record.
     *
     * Expected JSON body:
     * - employee_id (string)
     * - pay_period_start (Y-m-d)
     * - pay_period_end (Y-m-d)
     * - basic_salary (decimal) [used for payroll math]
     * - payment_method (optional, default bank_transfer)
     * - payment_date (optional, Y-m-d)
     * - allowances (optional decimal)
     * - bonuses (optional decimal)
     * - other_deductions (optional decimal)
     * - remarks (optional)
     */
    public function calculatePayslip()
    {
        $data = $this->getJsonData();
        if (!$data) {
            return $this->apiBadRequest('No data provided');
        }

        $rules = [
            'employee_id' => 'required|string',
            'pay_period_start' => 'required|valid_date[Y-m-d]',
            'pay_period_end' => 'required|valid_date[Y-m-d]',
            'basic_salary' => 'required|decimal',

            'allowances' => 'permit_empty|decimal',
            'bonuses' => 'permit_empty|decimal',
            'other_deductions' => 'permit_empty|decimal',

            'payment_method' => 'permit_empty|string|max_length[50]',
            'payment_date' => 'permit_empty|valid_date[Y-m-d]',
            'remarks' => 'permit_empty|string|max_length[2000]'
        ];

        if (!$this->validateInput($data, $rules)) {
            return $this->apiValidationError($this->getValidationErrors());
        }

        if (strtotime($data['pay_period_end']) < strtotime($data['pay_period_start'])) {
            return $this->apiValidationError(['pay_period_end' => 'End date cannot be earlier than start date.']);
        }

        $userId = $data['employee_id'];

        // Fetch timesheet for the period
        $timesheet = $this->timesheetModel->where([
            'user_id' => $userId,
            'start_date' => $data['pay_period_start'],
            'end_date' => $data['pay_period_end']
        ])->first();

        $basicSalary = (float) $data['basic_salary'];
        $regularHours = $timesheet ? (float) ($timesheet['regular_hours'] ?? 0) : 0;
        $otHours = $timesheet ? (float) ($timesheet['ot_hours'] ?? 0) : 0;
        $nightDiffHours = $timesheet ? (float) ($timesheet['night_diff'] ?? 0) : 0;
        $vacationLeave = $timesheet ? (float) ($timesheet['vac_leave'] ?? 0) : 0;
        $sickLeave = $timesheet ? (float) ($timesheet['sick_leave'] ?? 0) : 0;


        $allowances = (float) ($data['allowances'] ?? 0);
        $bonuses = (float) ($data['bonuses'] ?? 0);
        $otherDeductions = (float) ($data['other_deductions'] ?? 0);

        // Use shared calculator (preview + save/upsert in one service)
        $calculatorInput = [
            'employee_id' => $userId,
            'pay_period_start' => $data['pay_period_start'],
            'pay_period_end' => $data['pay_period_end'],
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'bonuses' => $bonuses,
            'other_deductions' => $otherDeductions,
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'payment_date' => $data['payment_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'status' => 'pending',
        ];

        $calculator = new \App\Services\PayslipCalculatorService();
        $calculated = $calculator->calculate($calculatorInput);
        $payslipId = $calculator->upsertPayslip($calculated);

        if (!$payslipId) {
            return $this->apiServerError('Failed to calculate payslip');
        }

        return $this->apiSuccess(['message' => 'Payslip calculated successfully', 'payslip_id' => (int) $payslipId], 201);
    }

    private function calculateSss(float $salary): float
    {
        if ($salary <= 0) {
            return 0.0;
        }
        return (float) min($salary * 0.045, 1800.00);
    }

    private function calculatePhilhealth(float $salary): float
    {
        if ($salary <= 0) {
            return 0.0;
        }
        return (float) min($salary * 0.03, 900.00);
    }

    private function calculateTax(float $salary): float
    {
        if ($salary <= 0) {
            return 0.0;
        }

        $annualSalary = $salary * 12;

        if ($annualSalary <= 250000) {
            $annualTax = 0.0;
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

        return (float) ($annualTax / 12);
    }
}

