<?php

namespace App\Auth\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/health', name: 'health', methods: ['GET', 'HEAD'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'healthy',
            'service' => 'auth-service',
            'timestamp' => time()
        ]);
    }
}

