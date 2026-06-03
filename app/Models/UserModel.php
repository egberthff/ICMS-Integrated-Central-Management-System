<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['username', 'password_hash', 'is_active', 'mfa_secret', 'mfa_enabled'];

    public function getUserByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    public function getUserById(string $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    public function createUser(string $username, string $passwordHash, bool $isActive = true): bool
    {
        $result = $this->insert([
            'username' => $username,
            'password_hash' => $passwordHash,
            'is_active' => $isActive
        ]);

        return $result !== false;
    }

    public function updateMFASecret(string $userId, string $secret, bool $enable = false): bool
    {
        return $this->update($userId, [
            'mfa_secret' => $secret,
            'mfa_enabled' => $enable
        ]) !== false;
    }

    public function deleteUser(string $userId): bool
    {
        return $this->delete($userId);
    }

    /**
     * Get a user (or all users) with their associated roles
     *
     * @param int|null $userId Optional specific user ID to filter by
     * @return array
     */
    /**
     * Get user(s) and their assigned roles dynamically.
     *
     * @param string|null $userId Optional specific user UUID
     * @return array|null Returns array of user data, single user payload array, or null if single user not found
     */
    public function getUsersWithRoles(?string $userId = null): mixed
    {
        // Case 1: Specific user retrieval
        if ($userId !== null) {
            $user = $this->find($userId);
            if (!$user) {
                return null;
            }

            $user['roles'] = $this->db->table('user_roles')
                ->select('roles.role_id, roles.role_name, roles.criticality_level')
                ->join('roles', 'roles.role_id = user_roles.role_id')
                ->where('user_roles.user_id', $userId)
                ->get()
                ->getResultArray();

            return $user;
        }

        // Case 2: Mass user retrieval (Get all users)
        $users = $this->findAll();
        if (empty($users)) {
            return [];
        }

        // Fetch all user-role mappings in a single bulk query to avoid N+1 query performance hits
        $allMappings = $this->db->table('user_roles')
            ->select('user_roles.user_id, roles.role_id, roles.role_name, roles.criticality_level')
            ->join('roles', 'roles.role_id = user_roles.role_id')
            ->get()
            ->getResultArray();

        // Group the roles data array by user_id for efficient memory mapping
        $groupedRoles = [];
        foreach ($allMappings as $mapping) {
            $groupedRoles[$mapping['user_id']][] = [
                'role_id' => $mapping['role_id'],
                'role_name' => $mapping['role_name'],
                'criticality_level' => $mapping['criticality_level'],
            ];
        }

        // Map grouped roles back to their respective user objects
        foreach ($users as &$user) {
            $user['roles'] = $groupedRoles[$user['user_id']] ?? [];
        }
        return $users;
    }
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
}