<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'permission_id';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['permission_key', 'description'];

    // Get permission by key
    public function getPermissionByKey(string $permissionKey): ?array
    {
        return $this->where('permission_key', $permissionKey)->first();
    }

    // Get permission by ID
    public function getPermissionById(string $permissionId): ?array
    {
        return $this->where('permission_id', $permissionId)->first();
    }

    // Create a new permission
    public function createPermission(string $permissionKey, string $description = ''): bool
    {
        return $this->insert([
            'permission_key' => $permissionKey,
            'description'    => $description
        ]);
    }

    // Delete permission
    public function deletePermission(string $permissionId): bool
    {
        return $this->delete($permissionId);
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
