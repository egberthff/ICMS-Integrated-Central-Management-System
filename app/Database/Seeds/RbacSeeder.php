<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Clear existing data
        $db->table('user_roles')->emptyTable();
        $db->table('role_permissions')->emptyTable();
        $db->table('permissions')->emptyTable();
        $db->table('roles')->emptyTable();
        $db->table('users')->emptyTable();

        // 2. Insert Mock Identity User (Password is 'secret123')
        $userId = 'usr_987654321';
        $db->table('users')->insert([
            'user_id'       => $userId,
            'username'      => 'jane.doe@company.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        // 3. Insert Roles
        $db->table('roles')->insertBatch([
            ['role_id' => 'r1', 'role_name' => 'employee', 'criticality_level' => 1],
            ['role_id' => 'r2', 'role_name' => 'payroll_admin', 'criticality_level' => 5],
        ]);

        // 4. Insert Permissions
        $db->table('permissions')->insertBatch([
            ['permission_id' => 'p1', 'permission_key' => 'profile:read', 'description' => 'View profile'],
            ['permission_id' => 'p2', 'permission_key' => 'timesheet:submit', 'description' => 'Submit hours'],
            ['permission_id' => 'p3', 'permission_key' => 'payroll:execute', 'description' => 'Disburse wages'],
        ]);

        // 5. Connect Permissions to Roles
        $db->table('role_permissions')->insertBatch([
            ['role_id' => 'r1', 'permission_id' => 'p1'], // Employee gets view profile
            ['role_id' => 'r1', 'permission_id' => 'p2'], // Employee gets submit timesheet
            ['role_id' => 'r2', 'permission_id' => 'p3'], // Payroll gets execute payroll
        ]);

        // 6. Assign both roles to Jane Doe account context
        $db->table('user_roles')->insertBatch([
            ['user_id' => $userId, 'role_id' => 'r1', 'assigned_at' => date('Y-m-d H:i:s')],
            ['user_id' => $userId, 'role_id' => 'r2', 'assigned_at' => date('Y-m-d H:i:s')],
        ]);
    }
}
