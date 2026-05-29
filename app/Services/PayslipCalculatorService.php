<?php

namespace App\Services;

use App\Models\PayslipModel;
use App\Models\TimesheetModel;
use App\Models\EmployeeModel;

class PayslipCalculatorService
{
    protected PayslipModel $payslipModel;
    protected TimesheetModel $timesheetModel;
    protected EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->payslipModel = new PayslipModel();
        $this->timesheetModel = new TimesheetModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function calculate(array $input): array
    {
        $employeeId = $input['employee_id'];
        $startDate = $input['pay_period_start'];
        $endDate = $input['pay_period_end'];

        $timesheet = $this->timesheetModel->where([
            'user_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])->first();

        // Employee info is currently not used for math, but we fetch it for future extensions.
        $this->employeeModel->where('user_id', $employeeId)->first();

        $basicSalary = floatval($input['basic_salary'] ?? 0);

        $otHours = $timesheet ? floatval($timesheet['ot_hours'] ?? 0) : 0;
        $nightDiffHours = $timesheet ? floatval($timesheet['night_diff'] ?? 0) : 0;

        $allowances = floatval($input['allowances'] ?? 0);
        $bonuses = floatval($input['bonuses'] ?? 0);
        $otherDeductions = floatval($input['other_deductions'] ?? 0);

        $hourlyRate = $basicSalary / (8 * 22); // Assuming 8 hours/day, 22 days/month
        $overtimePay = $hourlyRate * 1.25 * $otHours;
        $nightDiffPay = $hourlyRate * 0.10 * $nightDiffHours;
        $grossEarnings = $basicSalary + $overtimePay + $nightDiffPay + $allowances + $bonuses;

        $sssDeduction = $this->calculateSss($basicSalary);
        $philhealthDeduction = $this->calculatePhilhealth($basicSalary);
        $pagibigDeduction = 100.00;
        $taxDeduction = $this->calculateTax($basicSalary);
        $totalDeductions = $sssDeduction + $philhealthDeduction + $pagibigDeduction + $taxDeduction + $otherDeductions;
        $netPay = $grossEarnings - $totalDeductions;

        $ytdEarnings = $this->getYearToDateEarnings($employeeId, $endDate);
        $ytdDeductions = $this->getYearToDateDeductions($employeeId, $endDate);
        $ytdNet = $ytdEarnings - $ytdDeductions;

        $vacationLeave = $timesheet ? floatval($timesheet['vac_leave'] ?? 0) : 0;
        $sickLeave = $timesheet ? floatval($timesheet['sick_leave'] ?? 0) : 0;

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
            'payment_method' => $input['payment_method'] ?? 'bank_transfer',
            'payment_date' => $input['payment_date'] ?? null,
            'year_to_date_earnings' => $ytdEarnings,
            'year_to_date_deductions' => $ytdDeductions,
            'year_to_date_net' => $ytdNet,
            'leave_credits_vacation' => $vacationLeave,
            'leave_credits_sick' => $sickLeave,
            // status is controlled by payroll workflow; default is pending
            'status' => $input['status'] ?? 'pending',
            'remarks' => $input['remarks'] ?? null,
        ];
    }

    public function upsertPayslip(array $calculated): int
    {
        $existing = $this->payslipModel->where([
            'employee_id' => $calculated['employee_id'],
            'pay_period_start' => $calculated['pay_period_start'],
            'pay_period_end' => $calculated['pay_period_end'],
        ])->first();

        if ($existing && isset($existing['id'])) {
            $this->payslipModel->update($existing['id'], array_merge($calculated, ['id' => $existing['id']]));
            return (int) $existing['id'];
        }

        $payslipId = $this->payslipModel->insert($calculated);
        return (int) $payslipId;
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

    private function getYearToDateEarnings(string $employeeId, string $endDate): float
    {
        $year = substr($endDate, 0, 4);
        $startOfYear = $year . '-01-01';

        $payslips = $this->payslipModel->where([
            'employee_id' => $employeeId,
            'pay_period_start >=' => $startOfYear,
            'pay_period_end <=' => $endDate,
        ])->findAll();

        $total = 0.0;
        foreach ($payslips as $payslip) {
            $total += floatval($payslip['gross_earnings'] ?? 0);
        }

        return $total;
    }

    private function getYearToDateDeductions(string $employeeId, string $endDate): float
    {
        $year = substr($endDate, 0, 4);
        $startOfYear = $year . '-01-01';

        $payslips = $this->payslipModel->where([
            'employee_id' => $employeeId,
            'pay_period_start >=' => $startOfYear,
            'pay_period_end <=' => $endDate,
        ])->findAll();

        $total = 0.0;
        foreach ($payslips as $payslip) {
            $total += floatval($payslip['total_deductions'] ?? 0);
        }

        return $total;
    }
}

