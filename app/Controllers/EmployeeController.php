<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class EmployeeController extends BaseController
{
    public function submitHours(){
        return $this->response->setJSON(['message' => 'Employee API is operational']);
    }
}
