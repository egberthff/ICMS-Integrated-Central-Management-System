<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseApiController;
use App\Models\TimesheetModel;
use App\Models\PayslipModel;
use App\Services\PayslipCalculatorService;


class TimesheetProcessingController extends BaseApiController
{
    protected TimesheetModel $timesheetModel;
    protected PayslipModel $payslipModel;
    protected PayslipCalculatorService $calculator;

    public function __construct()
    {
        $this->timesheetModel = new TimesheetModel();
        $this->payslipModel = new PayslipModel();
        $this->calculator = new PayslipCalculatorService();
    }

    /**
     * Returns timesheet record for the given period.
     * Expected JSON body:
     * - employee_id (string)
     * - pay_period_start (Y-m-d)
     * - pay_period_end (Y-m-d)
     */
    public function getTimesheetForPeriod()
    {
        $data = $this->getJsonData(true);
        if (!$data) {
            return $this->apiBadRequest('No data provided');
        }

        $rules = [
            'employee_id' => 'required|string',
            'pay_period_start' => 'required|valid_date[Y-m-d]',
            'pay_period_end' => 'required|valid_date[Y-m-d]',
        ];

        if (!$this->validateInput($data, $rules)) {
            return $this->apiValidationError($this->getValidationErrors());
        }

        if (strtotime($data['pay_period_end']) < strtotime($data['pay_period_start'])) {
            return $this->apiValidationError(['pay_period_end' => 'End date cannot be earlier than start date']);
        }

        $timesheet = $this->timesheetModel->where([
            'user_id' => $data['employee_id'],
            'start_date' => $data['pay_period_start'],
            'end_date' => $data['pay_period_end'],
        ])->first();

        return $this->apiSuccess([
            'timesheet' => $timesheet,
        ]);
    }

    /**
     * Preview calculated payslip breakdown (NO SAVE).
     * Expected JSON body:
     * - employee_id (string)
     * - pay_period_start (Y-m-d)
     * - pay_period_end (Y-m-d)
     * - basic_salary (decimal)
     * Optional:
     * - allowances, bonuses, other_deductions
     * - payment_method, payment_date, remarks
     */
    public function previewPayslip()
    {
        $data = $this->getJsonData(true);
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
            'remarks' => 'permit_empty|string|max_length[2000]',
        ];

        if (!$this->validateInput($data, $rules)) {
            return $this->apiValidationError($this->getValidationErrors());
        }

        if (strtotime($data['pay_period_end']) < strtotime($data['pay_period_start'])) {
            return $this->apiValidationError(['pay_period_end' => 'End date cannot be earlier than start date']);
        }

        $calculated = $this->calculator->calculate([
            'employee_id' => $data['employee_id'],
            'pay_period_start' => $data['pay_period_start'],
            'pay_period_end' => $data['pay_period_end'],
            'basic_salary' => $data['basic_salary'],
            'allowances' => $data['allowances'] ?? 0,
            'bonuses' => $data['bonuses'] ?? 0,
            'other_deductions' => $data['other_deductions'] ?? 0,
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'payment_date' => $data['payment_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'status' => 'pending',
        ]);

        // readiness checks for UI
        $timesheet = $this->timesheetModel->where([
            'user_id' => $data['employee_id'],
            'start_date' => $data['pay_period_start'],
            'end_date' => $data['pay_period_end'],
        ])->first();

        $readiness = [
            'timesheet_found' => (bool) $timesheet,
            'timesheet_status' => $timesheet['status'] ?? null,
            'can_compute' => (bool) $timesheet,
            'warnings' => [],
        ];

        if (!$timesheet) {
            $readiness['warnings'][] = 'No timesheet found for the selected period.';
        } elseif (($timesheet['status'] ?? '') !== 'approved') {
            $readiness['warnings'][] = 'Timesheet is not approved yet. Payroll may use last approved values.';
        }

        return $this->apiSuccess([
            'readiness' => $readiness,
            'preview' => $calculated,
        ]);
    }

    /**
     * Get generated payslip for the period (view-only).
     * Expected JSON body:
     * - employee_id (string)
     * - pay_period_start (Y-m-d)
     * - pay_period_end (Y-m-d)
     */
    public function getPayslipForPeriod()
    {
        $data = $this->getJsonData(true);
        if (!$data) {
            return $this->apiBadRequest('No data provided');
        }

        $rules = [
            'employee_id' => 'required|string',
            'pay_period_start' => 'required|valid_date[Y-m-d]',
            'pay_period_end' => 'required|valid_date[Y-m-d]',
        ];

        if (!$this->validateInput($data, $rules)) {
            return $this->apiValidationError($this->getValidationErrors());
        }

        $payslip = $this->payslipModel->where([
            'employee_id' => $data['employee_id'],
            'pay_period_start' => $data['pay_period_start'],
            'pay_period_end' => $data['pay_period_end'],
        ])->first();

        return $this->apiSuccess([
            'payslip' => $payslip,
        ]);
    }
}

