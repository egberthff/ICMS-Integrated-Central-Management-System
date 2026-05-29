<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;

/**
 * Base API Controller provides common functionality for all API controllers
 * to reduce code duplication across HRMS modules.
 */
abstract class BaseApiController extends BaseController
{
    use \CodeIgniter\API\ResponseTrait;

    /**
     * Get JSON data from request with validation
     *
     * @param bool $asArray Whether to return data as array (default: true)
     * @return mixed JSON data or null if invalid
     */
    protected function getJsonData(bool $asArray = true)
    {
        try {
            $data = $this->request->getJSON($asArray);
            if ($data === null && $asArray) {
                return [];
            }
            return $data;
        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
            log_message('error', 'JSON parse error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate input data against rules
     *
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return bool True if validation passes
     */
    protected function validateInput(array $data, array $rules): bool
    {
        // Use CodeIgniter's validation service
        $validation = \Config\Services::validation();
        return $validation->setRules($rules)
            ->run($data);
    }

    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    protected function getValidationErrors(): array
    {
        $validation = \Config\Services::validation();
        return $validation->getErrors();
    }

    /**
     * Extract Bearer token from Authorization header
     *
     * @return string|null Bearer token or null if not present/invalid
     */
    protected function getBearerToken(): ?string
    {
        $authHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        // log_message('error', $authHeader);
        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Decode JWT token
     *
     * @param string $token JWT token
     * @return object|null Decoded token or null if invalid
     */
    protected function decodeJwt(string $token)
    {
        try {
            return \Config\Services::jwtDecoder($token);
        } catch (\Exception $e) {
            log_message('error', 'JWT decode error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Standardized success response
     *
     * @param array $data Response data
     * @param int $httpCode HTTP status code (default: 200)
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiSuccess(array $data = [], int $httpCode = 200)
    {
        $response = [
            'status' => $httpCode,
            'message' => 'Success',
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return $this->respond($response, $httpCode);
    }

    /**
     * Standardized created response
     *
     * @param array $data Response data
     * @param string $message Success message (default: 'Created successfully')
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiCreated(array $data = [], string $message = 'Created successfully')
    {
        $response = [
            'status' => 201,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return $this->respondCreated($response);
    }

    /**
     * Standardized validation error response
     *
     * @param array $errors Validation errors
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiValidationError(array $errors)
    {
        return $this->failValidationErrors($errors);
    }

    /**
     * Standardized not found response
     *
     * @param string $message Error message (default: 'Resource not found')
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiNotFound(string $message = 'Resource not found')
    {
        return $this->failNotFound($message);
    }

    /**
     * Standardized unauthorized response
     *
     * @param string $message Error message (default: 'Unauthorized')
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiUnauthorized(string $message = 'Unauthorized')
    {
        return $this->failUnauthorized($message);
    }

    /**
     * Standardized server error response
     *
     * @param string $message Error message (default: 'Internal server error')
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiServerError(string $message = 'Internal server error')
    {
        return $this->failServerError($message);
    }

    /**
     * Standardized bad request response
     *
     * @param string $message Error message (default: 'Bad request')
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function apiBadRequest(string $message = 'Bad request')
    {
        return $this->fail($message, 400);
    }

    /**
     * Initialize controller - override to add API-specific initialization
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Call parent initialization
        parent::initController($request, $response, $logger);

        // API-specific initialization can go here
        // For example, you can set default headers / formatting here.
        // Note: setFormat() is not available in this project's controller inheritance.
    }
}