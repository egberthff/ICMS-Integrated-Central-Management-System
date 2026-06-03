<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['role_name', 'criticality_level'];

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
    public function saveRoleData(string $roleName, int $criticalityLevel = 1, ?string $roleId = null, string $action): bool
    {
        if ($roleId && $action === 'delete') {
            return $this->delete($roleId);
        }

        $data = [
            'role_name' => $roleName,
            'criticality_level' => $criticalityLevel
        ];

        if ($roleId && $action === 'edit') {
            return $this->update($roleId, $data);
        }


        // Otherwise, perform regular insert statement
        return $this->insert($data) !== false;

    }

    public function updateRole(string $roleId, $updateData): bool
    {
        return $this->update($roleId, $updateData);
    }

    // Delete role
    public function deleteRole(string $roleId): bool
    {
        return $this->delete($roleId);
    }

    //Get roles with associated permissions
    public function getRolesWithPermissions(?string $roleId = null): mixed
    {
        // Case 1: Specific role retrieval
        if ($roleId !== null) {
            $role = $this->find($roleId);
            if (!$role) {
                return null;
            }
            $role['permissions'] = $this->db->table('role_permissions')
                ->select('permissions.permission_id, permissions.permission_key, permissions.description')
                ->join('permissions', 'permissions.permission_id = role_permissions.permission_id')
                ->where('role_permissions.role_id', $roleId)
                ->get()
                ->getResultArray();

            return $role;
        }

        // Case 2: Mass role retrieval (Get all roles)
        $roles = $this->findAll();
        if (empty($roles)) {
            return [];
        }

        // Fetch all role-permission mappings in a single bulk query
        $allMappings = $this->db->table('role_permissions')
            ->select('role_permissions.role_id, permissions.permission_id, permissions.permission_key, permissions.description')
            ->join('permissions', 'permissions.permission_id = role_permissions.permission_id')
            ->get()
            ->getResultArray();

        // Group the permissions data array by role_id in PHP memory
        $groupedPermissions = [];
        foreach ($allMappings as $mapping) {
            $groupedPermissions[$mapping['role_id']][] = [
                'permission_id' => $mapping['permission_id'],
                'permission_key' => $mapping['permission_key'],
                'description' => $mapping['description'],
            ];
        }

        // Map grouped permissions back to their respective role objects
        foreach ($roles as &$role) {
            $role['permissions'] = $groupedPermissions[$role['role_id']] ?? [];
        }
        $data['roles'] = $roles;
        return $roles;
    }

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
}
