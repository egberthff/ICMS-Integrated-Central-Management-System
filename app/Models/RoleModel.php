<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'role_id';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['role_name', 'criticality_level'];

    // Get role by name
    public function getRoleByName(string $roleName): ?array
    {
        return $this->where('role_name', $roleName)->first();
    }

    // Get role by ID
    public function getRoleById(string $roleId): ?array
    {
        return $this->where('role_id', $roleId)->first();
    }

    // Create a new role
    public function createRole(string $roleName, int $criticalityLevel = 1): bool
    {
        return $this->insert([
            'role_name'         => $roleName,
            'criticality_level' => $criticalityLevel
        ]);
    }

    // Delete role
    public function deleteRole(string $roleId): bool
    {
        return $this->delete($roleId);
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
