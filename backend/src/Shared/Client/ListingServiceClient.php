<?php

namespace App\Shared\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client to communicate with listing-service
 */
class ListingServiceClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $listingServiceUrl
    ) {
    }

    /**
     * Notify listing-service to refresh ES index for a listing
     */
    public function refreshIndex(int $listingId): bool
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                $this->listingServiceUrl . '/api/listings/' . $listingId . '/refresh-index',
                ['timeout' => 5]
            );

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->warning('Failed to refresh listing index: ' . $e->getMessage(), [
                'listing_id' => $listingId
            ]);
            return false;
        }
    }
}

