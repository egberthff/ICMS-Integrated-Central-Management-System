<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PayrollController extends BaseController
{
    public function disburse()
    {
        return $this->response->setJSON(['message' => 'Payroll API is operational']);
    }

    public function summary()
    {
        return $this->response->setJSON(['message' => 'Payroll summary endpoint is operational']);
    }
}
