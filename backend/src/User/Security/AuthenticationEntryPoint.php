<?php

namespace App\User\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            [
                'error' => 'Kimlik doğrulama gerekli',
                'message' => 'Bu kaynağa erişmek için giriş yapmanız gerekiyor'
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}

