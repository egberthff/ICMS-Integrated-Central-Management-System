<?php

namespace App\Controllers;

use App\Controllers\BaseApiController;
use App\Models\TimesheetModel;

class EmployeeController extends BaseApiController
{
    public function getTimesheet()
    {
        $data = $this->getJsonData();
        // log_message('error', print_r($data,true));

        $authHeader = $this->getBearerToken();

        return $this->apiSuccess([
            'message' => "Employee Timesheet page access allowed.",
            'token' => $authHeader
        ]);
    }

    public function submitTimesheet()
    {
        $requestData = $this->getJsonData(true);
        // log_message('error', print_r($data, true));
        // If the body is completely empty, reject it immediately
        if (empty($requestData)) {
            return $this->apiBadRequest('No data provided');
        }
        // 2. Define the exact rules matching your database migration constraints
        $rules = [
            'user_id' => 'required|string',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
            'days_worked' => 'required|numeric|greater_than_equal_to[0]',
            'regular_hours' => 'required|numeric|greater_than_equal_to[0]',
            'ot_hours' => 'required|numeric|greater_than_equal_to[0]',
            'night_diff' => 'required|numeric|greater_than_equal_to[0]',
            'sick_leave' => 'required|numeric|greater_than_equal_to[0]',
            'vac_leave' => 'required|numeric|greater_than_equal_to[0]',
            'unpaid_leave' => 'required|numeric|greater_than_equal_to[0]',
            'notes' => 'permit_empty|string|max_length[1000]'
        ];
        // 3. Execute validation against the JSON data array
        if (!$this->validateInput($requestData, $rules)) {
            // Automatically returns a 400 Bad Request with a list of specific structural validation errors
            return $this->apiValidationError($this->getValidationErrors());
        }

        // 4. (Optional) Custom business logic check: Ensure end date is not before start date
        if (strtotime($requestData['end_date']) < strtotime($requestData['start_date'])) {
            return $this->apiValidationError([
                'end_date' => 'The end date cannot be earlier than the start date.'
            ]);
        }

        unset($requestData['username'], $requestData['activeRole']);
        // 5. Data is completely clean! Proceed to save into your Database Model here
        try {

            $timesheetModel = new TimesheetModel();
            if (!$timesheetModel->insert($requestData)) {
                return $this->apiServerError('Database transaction processing failed');
            }
            $authHeader = $this->getBearerToken();
            return $this->apiSuccess([
                'message' => 'Timesheet validated and processed successfully.',
                'data' => $requestData
            ]);

        } catch (\Exception $e) {
            return $this->apiServerError('Database transaction processing failed.');
        }
    }

    public function submitHours()
    {
        return $this->apiSuccess(['message' => 'Employee API is operational']);
    }
}
