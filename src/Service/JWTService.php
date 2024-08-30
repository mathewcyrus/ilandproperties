<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JWTService
{
    private string $secretKey;
    private $algorithm;

    public function __construct(string $secretKey, string $algorithm)
    {
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
    }

    public function createToken($payload)
    {
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function decodeToken($jwt)
    {
        try {
            return JWT::decode($jwt, new Key($this->secretKey, $this->algorithm));
        } catch (ExpiredException $e) {
            return new JsonResponse(['error' => 'Token expired'], Response::HTTP_UNAUTHORIZED);
        } catch (SignatureInvalidException $e) {
            return new JsonResponse(['error' => 'Invalid token signature'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function getUserFromToken($jwt)
    {
        $decoded = $this->decodeToken($jwt);
        if ($decoded instanceof JsonResponse) {
            return $decoded;
        }

        return $decoded->user_id;
    }
}
