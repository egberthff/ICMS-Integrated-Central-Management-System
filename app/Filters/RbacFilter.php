<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

class RbacFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {

        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $token = $request->getCookie('authToken');
        if (!$token) {
            return Services::response()->setJSON(['error' => 'Missing token session'])->setStatusCode(401);
        }

        try {
            $decoded = Services::jwtDecoder($token); // Decodes token payload
            // Inject decoded context directly into request for controller access
            $request->activeTokenContext = $decoded;
        } catch (Exception $e) {
            return Services::response()->setJSON(['error' => 'Invalid session token'])->setStatusCode(401);
        }
        if (!empty($arguments)) {
            // Replace pipe separator back to colon for permission check (e.g., 'payroll|execute' -> 'payroll:execute')
            $requiredPermission = str_replace('|', ':', $arguments[0]);
            // Enforce that active token array contains permission
            if (!in_array($requiredPermission, $decoded->permissions)) {
                return Services::response()
                    ->setJSON(['error' => 'Forbidden: Insufficient permissions for active role context'])
                    ->setStatusCode(403);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing required
    }
}
