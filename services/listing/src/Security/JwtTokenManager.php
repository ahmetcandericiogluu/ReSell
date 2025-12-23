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
            error_log('[JwtTokenManager] Secret length: ' . strlen($this->secret));
            error_log('[JwtTokenManager] Secret first 10 chars: ' . substr($this->secret, 0, 10) . '...');
            $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
            return (array) $decoded;
        } catch (\Exception $e) {
            error_log('[JwtTokenManager] Decode error: ' . $e->getMessage());
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

