<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenManager
{
    public function __construct(
        private readonly string $secret
    ) {
    }

    public function decode(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}

