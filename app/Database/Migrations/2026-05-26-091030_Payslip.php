<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Payslip extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id' => ['type' => 'VARCHAR', 'constraint' => 255],
            'pay_period_start' => ['type' => 'DATE'],
            'pay_period_end' => ['type' => 'DATE'],
            'date_issued' => ['type' => 'DATE'],
            'basic_salary' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'overtime_pay' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'allowances' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'bonuses' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'gross_earnings' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'sss_deduction' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'philhealth_deduction' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'pagibig_deduction' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'tax_deduction' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'other_deductions' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'total_deductions' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'net_pay' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'bank_transfer'],
            'payment_date' => ['type' => 'DATE', 'null' => true],
            'year_to_date_earnings' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'year_to_date_deductions' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'year_to_date_net' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'leave_credits_vacation' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'leave_credits_sick' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'pending', 'approved', 'paid', 'rejected'], 'default' => 'draft'],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true]
        ]);
        
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('employee_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payslips');
    }
    
    public function down()
    {
        $this->forge->dropTable('payslips');
    }
}