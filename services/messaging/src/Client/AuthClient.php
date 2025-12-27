<?php

namespace App\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class AuthClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $authServiceUrl
    ) {
    }

    /**
     * Get current user info from auth-service using JWT token
     * 
     * @return array{id: int, email: string, name: ?string}|null
     */
    public function getCurrentUser(string $bearerToken): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $this->authServiceUrl . '/api/auth/me', [
                'headers' => [
                    'Authorization' => $bearerToken,
                    'Accept' => 'application/json',
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            
            return [
                'id' => (int) $data['id'],
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? null,
            ];
        } catch (HttpExceptionInterface $e) {
            $this->logger->warning('Auth service returned error: ' . $e->getMessage());
            return null;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to connect to auth service: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Auth client error: ' . $e->getMessage());
            return null;
        }
    }
}

