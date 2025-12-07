<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenManager
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private readonly string $secret
    ) {
    }

    public function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid token: ' . $e->getMessage());
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            $this->decodeToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

