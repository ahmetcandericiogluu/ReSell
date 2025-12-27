<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
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
            throw new CustomUserMessageAuthenticationException('Missing or invalid Authorization header');
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtTokenManager->decode($token);

        if (!$payload) {
            throw new CustomUserMessageAuthenticationException('Invalid or expired token');
        }

        $userId = $payload['user_id'] ?? $payload['sub'] ?? null;
        $email = $payload['email'] ?? '';
        $name = $payload['name'] ?? null;

        if (!$userId) {
            throw new CustomUserMessageAuthenticationException('Invalid token payload');
        }

        // Store user info in request for easy access in controllers
        $request->attributes->set('user_id', (int) $userId);
        $request->attributes->set('user_name', $name);

        return new SelfValidatingPassport(
            new UserBadge($email, fn() => new JwtUser((int) $userId, $email, $name))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Continue to controller
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => 'Authentication required',
            'message' => $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}

