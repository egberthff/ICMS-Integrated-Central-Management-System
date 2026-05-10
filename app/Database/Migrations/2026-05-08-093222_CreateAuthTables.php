<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTables extends Migration
{
    public function up()
    {
        // 1. Core Users Table
        $this->forge->addField([
            'user_id'       => ['type' => 'CHAR', 'constraint' => 36, 'default' => new \CodeIgniter\Database\RawSql('(UUID())')],
            'username'      => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_active'     => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'    => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('user_id');
        $this->forge->createTable('users');

        // 2. Master Roles Table
        $this->forge->addField([
            'role_id'           => ['type' => 'CHAR', 'constraint' => 36, 'default' => new \CodeIgniter\Database\RawSql('(UUID())')],
            'role_name'         => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'criticality_level' => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
        ]);
        $this->forge->addPrimaryKey('role_id');
        $this->forge->createTable('roles');

        // 3. Master Permissions Table
        $this->forge->addField([
            'permission_id'  => ['type' => 'CHAR', 'constraint' => 36, 'default' => new \CodeIgniter\Database\RawSql('(UUID())')],
            'permission_key' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'description'    => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('permission_id');
        $this->forge->createTable('permissions');

        // 4. Role-Permission Mapping
        $this->forge->addField([
            'role_id'       => ['type' => 'CHAR', 'constraint' => 36],
            'permission_id' => ['type' => 'CHAR', 'constraint' => 36],
        ]);
        $this->forge->addPrimaryKey(['role_id', 'permission_id']);
        $this->forge->addForeignKey('role_id', 'roles', 'role_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'permission_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions');

        // 5. User-Role Assignment
        $this->forge->addField([
            'user_id'     => ['type' => 'CHAR', 'constraint' => 36],
            'role_id'     => ['type' => 'CHAR', 'constraint' => 36],
            'assigned_at' => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey(['user_id', 'role_id']);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'role_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_roles');

        // 6. Toxic Role Combinations
        $this->forge->addField([
            'role_id_a'     => ['type' => 'CHAR', 'constraint' => 36],
            'role_id_b'     => ['type' => 'CHAR', 'constraint' => 36],
            'error_message' => ['type' => 'TEXT'],
        ]);
        $this->forge->addPrimaryKey(['role_id_a', 'role_id_b']);
        $this->forge->addForeignKey('role_id_a', 'roles', 'role_id');
        $this->forge->addForeignKey('role_id_b', 'roles', 'role_id');
        $this->forge->createTable('toxic_role_combinations');
    }

    public function down()
    {
        // Tables dropped in reverse order to respect foreign key constraints
        $this->forge->dropTable('toxic_role_combinations', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('users', true);
    }
}
