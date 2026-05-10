<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table            = 'user_roles';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'role_id', 'assigned_at'];

    // Assign a role to a user
    public function assignRoleToUser(string $userId, string $roleId): bool
    {
        // Check if already assigned
        $existing = $this->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if ($existing) {
            return false; // Already assigned
        }

        $result = $this->insert([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'assigned_at' => date('Y-m-d H:i:s')
        ]);

        return $result !== false; 
    }

    // Remove a role from a user
    public function removeRoleFromUser(string $userId, string $roleId): bool
    {
        return $this->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
    }

    // Get all roles for a specific user (with role details)
    public function getUserRoles(string $userId): array
    {
        $builder = $this->db->table('user_roles');
        $builder->select('roles.role_id, roles.role_name, roles.criticality_level, user_roles.assigned_at');
        $builder->join('roles', 'roles.role_id = user_roles.role_id');
        $builder->where('user_roles.user_id', $userId);

        $query = $builder->get();
        return $query->getResultArray();
    }

    // Check if user has a specific role
    public function userHasRole(string $userId, string $roleId): bool
    {
        return $this->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->countAllResults() > 0;
    }

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
