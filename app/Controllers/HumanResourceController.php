<?php

namespace App\Controllers;

use App\Controllers\BaseApiController;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

class HumanResourceController extends BaseApiController
{
    public function addNewEmployee()
    {
        $reqeustToken = $this->request->activeTokenContext;
        $active_role = $reqeustToken->active_role ?? null;
        $permissionArray = $reqeustToken->permissions ?? [];

        $hasValidRole = ($active_role === 'hr:add_employee');
        $hasValidPermission = is_array($permissionArray) && in_array('hr:add_employee', $permissionArray);

        if (!$hasValidRole && !$hasValidPermission) {
            return $this->apiForbidden("You don't have access to this action");
        }

        $data = $this->getJsonData(true);
        $rules = [
            'user_id' => 'required|string|max_length[255]',
            'employee_number' => 'required|string',
            'first_name' => 'required|string|max_length[30]',
            'last_name' => 'required|string|max_length[30]',
            'position_title' => 'required|string',
            'employment_status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validateInput($data, $rules)) {
            return $this->apiValidationError($this->getValidationErrors());
        }

        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Strips tags and encodes special characters
                $sanitizedData[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitizedData[$key] = $value;
            }
        }

        try {
            $emplooyeesModel = new EmployeeModel();
            if (!$emplooyeesModel->insert($sanitizedData)) {
                return $this->apiServerError('Database transaction processing failed');
            }

            return $this->apiSuccess([
                'message' => 'Employee added successfully.',
                'data' => $sanitizedData
            ], 200);
        } catch (Exception $e) {

        }


    }
}
