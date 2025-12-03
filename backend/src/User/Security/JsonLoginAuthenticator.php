<?php

namespace App\User\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JsonLoginAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        // This authenticator should only be used for session-based requests
        // We don't want to authenticate every request, only when user is logged in
        return $request->hasSession() && $request->getSession()->has('_security_main');
    }

    public function authenticate(Request $request): Passport
    {
        // This is called when user has a session
        // We just need to load the user from session
        return new SelfValidatingPassport(
            new UserBadge($request->getSession()->get('_security_user_identifier'))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Return null to let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => 'Kimlik doğrulama başarısız'
        ], Response::HTTP_UNAUTHORIZED);
    }
}

