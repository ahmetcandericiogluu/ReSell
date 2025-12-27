<?php

namespace App\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class ListingClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $listingServiceUrl
    ) {
    }

    /**
     * Get listing details from listing-service
     * 
     * @return array{id: int, seller_id: int, title: string}|null
     */
    public function getListing(int $listingId): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $this->listingServiceUrl . '/api/listings/' . $listingId, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            
            // Handle both direct response and nested response formats
            $listing = $data['data'] ?? $data;
            
            return [
                'id' => (int) ($listing['id'] ?? $listingId),
                'seller_id' => (int) ($listing['seller_id'] ?? $listing['sellerId'] ?? 0),
                'title' => $listing['title'] ?? 'Untitled',
            ];
        } catch (HttpExceptionInterface $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            if ($statusCode === 404) {
                $this->logger->info('Listing not found: ' . $listingId);
            } else {
                $this->logger->warning('Listing service returned error: ' . $e->getMessage());
            }
            return null;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to connect to listing service: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Listing client error: ' . $e->getMessage());
            return null;
        }
    }
}

