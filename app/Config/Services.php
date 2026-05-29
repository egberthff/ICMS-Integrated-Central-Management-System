<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    public static function jwtEncoder(array $payload): string
    {
        $secretKey = env('JWT_SECRET_KEY', 'X95GTUpmu2YO6ezA065JBOolos/ImQI1v4MV0AR4/Jg=');

        // Add standard JWT timestamps to the passed data payload
        $payload['iat'] = time();
        $payload['exp'] = $payload['exp'] ?? (time() + 3600); // Default to 1 hour expiration
        return JWT::encode($payload, $secretKey, 'HS256');
    }

    public static function jwtDecoder(string $token): object
    {
        $secretKey = env('JWT_SECRET_KEY', 'X95GTUpmu2YO6ezA065JBOolos/ImQI1v4MV0AR4/Jg=');
        try {
            return JWT::decode($token, new Key($secretKey, 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token session.');
        }
    }

    // public static function jwtDecoder(string $token): object
    // {
    //     $secretKey = env('JWT_SECRET_KEY');

    //     try {
    //         return JWT::decode($token, new Key($secretKey, 'HS256'));
    //     } catch (ExpiredException $e) {
    //         // Explicitly handle token expiration timestamps
    //         throw new \Exception('Your session has expired. Please log in again.');
    //     } catch (SignatureInvalidException $e) {
    //         // Explicitly handle tampering or signature secret mismatch
    //         throw new \Exception('Invalid token signature. Access denied.');
    //     } catch (\Exception $e) {
    //         // Capture structural errors (e.g. "Wrong number of segments")
    //         throw new \Exception('Token decoding failed: ' . $e->getMessage());
    //     }
    // }
}
