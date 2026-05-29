<?php

namespace App\Models;

use CodeIgniter\Model;

class TimesheetModel extends Model
{
    protected $table = 'timesheet';

    protected $primaryKey = 'timesheet_id';
    // protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'start_date', 'end_date', 'days_worked', 'regular_hours', 'ot_hours', 'night_diff', 'sick_leave', 'vac_leave', 'unpaid_leave', 'notes', 'status'];

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
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function insert($row = null, bool $returnID = true)
    {
        $result = parent::insert($row, $returnID);

        return $result !== false;
    }
}

