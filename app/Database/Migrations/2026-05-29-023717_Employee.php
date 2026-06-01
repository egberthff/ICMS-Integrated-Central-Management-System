<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Employee extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // External/Business identifier (explicitly requested; no reliance on auto-increment id)
            'employee_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'default' => new \CodeIgniter\Database\RawSql('(UUID())'),
            ],

            // Link to authentication user
            'user_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],

            // Basic employee profile
            'employee_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'position_title' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],

            // HR status used by payroll processing
            'employment_status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
            ],

            // Audit fields
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);

        // Primary key
        $this->forge->addPrimaryKey('employee_id');

        // FK to users table (auth)
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');

        // Create table (per requirement)
        $this->forge->createTable('employees');
    }

    public function down()
    {
        // Drop in reverse order of constraints
        $this->forge->dropTable('employeed', true);
    }
}

