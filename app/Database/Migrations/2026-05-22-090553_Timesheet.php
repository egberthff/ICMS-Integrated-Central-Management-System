<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Timesheet extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'timesheet_id'  => ['type' => 'CHAR', 'constraint' => 36, 'default' => new \CodeIgniter\Database\RawSql('(UUID())')],
            'user_id'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'start_date'    => ['type' => 'DATE'],
            'end_date'      => ['type' => 'DATE'],
            // FIXED DECIMAL PRECISION: Allow up to 3 total digits with 2 decimal places (e.g., 15.50 days or 120.75 hours)
            'days_worked'   => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'regular_hours' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'ot_hours'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'night_diff'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'sick_leave'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'vac_leave'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'unpaid_leave'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'notes'         => ['type' => 'TEXT', 'null' => true], // FIXED: Allow notes to be optional
            // AUDIT TRAIL FIELDS
            'status'        => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')],
        ]);
        
        $this->forge->addPrimaryKey('timesheet_id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('timesheet');
    }

    public function down()
    {
        $this->forge->dropTable('timesheet');
    }
}
