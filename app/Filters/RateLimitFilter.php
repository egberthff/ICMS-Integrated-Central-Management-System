<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RateLimitFilter implements FilterInterface
{
    /**
     * Maximum number of attempts allowed
     */
    protected int $maxAttempts = 5;

    /**
     * Time period in seconds for the rate limit
     */
    protected int $timePeriod = 900; // 15 minutes

    /**
     * Cache key prefix for rate limiting
     */
    protected string $cachePrefix = 'rate_limit_';

    public function before(RequestInterface $request, $arguments = null)
    {
        // Only apply rate limiting to login endpoint
        if ($request->getMethod() !== 'post' || $request->uri->getPath() !== '/login') {
            return null; // Skip filtering for non-login requests
        }

        // Get client IP address
        $ipAddress = $this->getIPAddress();

        // Create cache key for this IP
        $cacheKey = $this->cachePrefix . $ipAddress;

        // Get current attempt count from cache
        $cache = Services::cache();
        $attempts = $cache->get($cacheKey);

        if ($attempts === null) {
            $attempts = 0;
        }

        // Check if limit exceeded
        if ($attempts >= $this->maxAttempts) {
            // Calculate time until reset
            $ttl = $cache->getMetadata($cacheKey)['expire'] - time();
            $minutes = max(1, ceil($ttl / 60));

            return Services::response()
                ->setJSON([
                    'error' => 'Too many login attempts. Please try again later.',
                    'retry_after' => $minutes
                ])
                ->setStatusCode(429); // Too Many Requests
        }

        // Allow request to proceed
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Only track failed login attempts
        if ($request->getMethod() !== 'post' || $request->uri->getPath() !== '/login') {
            return;
        }

        // If response indicates failed authentication (401 or validation error), increment counter
        $statusCode = $response->getStatusCode();
        if ($statusCode === 401 || $statusCode === 422) {
            $ipAddress = $this->getIPAddress();
            $cacheKey = $this->cachePrefix . $ipAddress;

            $cache = Services::cache();
            $attempts = $cache->get($cacheKey);

            if ($attempts === null) {
                $attempts = 0;
            }

            $attempts++;

            // Store updated count with expiration
            $cache->save($cacheKey, $attempts, $this->timePeriod);
        }
    }

    /**
     * Get client IP address
     */
    private function getIPAddress(): string
    {
        // Check for forwarded IPs first (behind proxy/load balancer)
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, get the first one
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        // Validate IP address
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return 'unknown';
    }
}