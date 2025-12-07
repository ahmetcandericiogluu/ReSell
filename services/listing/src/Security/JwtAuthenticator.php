<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly JwtTokenManager $jwtTokenManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('Missing or invalid Authorization header');
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        try {
            $payload = $this->jwtTokenManager->decodeToken($token);
            
            // Extract user ID from JWT payload
            $userId = $payload['sub'] ?? null;
            
            if (!$userId) {
                throw new AuthenticationException('Invalid token payload');
            }

            // Store the payload in request attributes for later use
            $request->attributes->set('jwt_payload', $payload);
            $request->attributes->set('user_id', $userId);

            return new SelfValidatingPassport(
                new UserBadge((string) $userId, function ($userIdentifier) use ($payload) {
                    // Return a simple user object (we don't need DB lookup)
                    return new JwtUser((int) $userIdentifier, $payload);
                })
            );
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid token: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => 'Authentication failed',
            'message' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}

