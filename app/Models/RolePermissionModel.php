<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table            = 'role_permissions';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['role_id', 'permission_id'];

    // Assign a permission to a role
    public function assignPermissionToRole(string $roleId, string $permissionId): bool
    {
        // Check if already assigned
        $existing = $this->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->first();

        if ($existing) {
            return false; // Already assigned
        }

        $result = $this->insert([
            'role_id'       => $roleId,
            'permission_id' => $permissionId
        ]);
        return $result !== false;
    }

    // Remove a permission from a role
    public function removePermissionFromRole(string $roleId, string $permissionId): bool
    {
        return $this->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    // Get all permissions for a specific role (with permission details)
    public function getRolePermissions(string $roleId): array
    {
        $builder = $this->db->table('role_permissions');
        $builder->select('permissions.permission_id, permissions.permission_key, permissions.description');
        $builder->join('permissions', 'permissions.permission_id = role_permissions.permission_id');
        $builder->where('role_permissions.role_id', $roleId);

        $query = $builder->get();
        return $query->getResultArray();
    }

    // Check if a role has a specific permission
    public function roleHasPermission(string $roleId, string $permissionId): bool
    {
        return $this->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
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
