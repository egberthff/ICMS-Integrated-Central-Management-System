<?php

namespace App\Models;

use CodeIgniter\Model;

class PayslipModel extends Model
{
    protected $table = 'payslips';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'employee_id',
        'pay_period_start',
        'pay_period_end',
        'date_issued',
        'basic_salary',
        'overtime_pay',
        'allowances',
        'bonuses',
        'gross_earnings',
        'sss_deduction',
        'philhealth_deduction',
        'pagibig_deduction',
        'tax_deduction',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'payment_method',
        'payment_date',
        'year_to_date_earnings',
        'year_to_date_deductions',
        'year_to_date_net',
        'leave_credits_vacation',
        'leave_credits_sick',
        'status',
        'remarks'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Get payslips for a specific employee
    public function getEmployeePayslips($employeeId, $limit = null, $offset = null)
    {
        $builder = $this->where('employee_id', $employeeId)
                       ->orderBy('pay_period_end', 'DESC');

        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    // Get a single payslip by ID
    public function getPayslipById($id)
    {
        return $this->where('id', $id)->first();
    }

    // Get latest payslip for employee
    public function getLatestPayslip($employeeId)
    {
        return $this->where('employee_id', $employeeId)
                   ->orderBy('pay_period_end', 'DESC')
                   ->first();
    }

    // Search payslips with filters
    public function searchPayslips($employeeId, $filters = [])
    {
        $builder = $this->where('employee_id', $employeeId);

        if (isset($filters['start_date']) && $filters['start_date']) {
            $builder->where('pay_period_start >=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $builder->where('pay_period_end <=', $filters['end_date']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('pay_period_end', 'DESC')
                      ->get()
                      ->getResultArray();
    }
}