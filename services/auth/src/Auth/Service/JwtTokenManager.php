<?php

namespace App\Auth\Service;

use App\Auth\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenManager
{
    private const ALGORITHM = 'HS256';
    private const TOKEN_TTL = 86400; // 24 hours

    public function __construct(
        private readonly string $secret
    ) {
    }

    public function createToken(User $user): string
    {
        $now = time();
        
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'iat' => $now,
            'exp' => $now + self::TOKEN_TTL,
        ];

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    public function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid token: ' . $e->getMessage());
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

