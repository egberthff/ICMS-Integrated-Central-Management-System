<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'password_hash', 'is_active'];

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
                'username'      => $username,
                'password_hash' => $passwordHash,
                'is_active'     => $isActive
            ]);

            return $result !== false;
        }
    
        public function deleteUser(string $userId): bool
        {
            return $this->delete($userId);
        }

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

}
