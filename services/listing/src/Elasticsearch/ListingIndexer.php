<?php

namespace App\Elasticsearch;

use App\Entity\Listing;
use Psr\Log\LoggerInterface;

class ListingIndexer
{
    public const INDEX_NAME = 'listings_v1';

    public function __construct(
        private readonly ElasticsearchClient $esClient,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getIndexMapping(): array
    {
        return [
            'mappings' => [
                'properties' => [
                    'id' => ['type' => 'keyword'],
                    'seller_id' => ['type' => 'integer'],
                    'category_id' => ['type' => 'integer'],
                    'title' => [
                        'type' => 'text',
                        'fields' => ['keyword' => ['type' => 'keyword']]
                    ],
                    'description' => ['type' => 'text'],
                    'price' => ['type' => 'scaled_float', 'scaling_factor' => 100],
                    'currency' => ['type' => 'keyword'],
                    'status' => ['type' => 'keyword'],
                    'location' => [
                        'type' => 'text',
                        'fields' => ['keyword' => ['type' => 'keyword']]
                    ],
                    'images' => [
                        'type' => 'nested',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'url' => ['type' => 'keyword'],
                            'position' => ['type' => 'integer']
                        ]
                    ],
                    'created_at' => ['type' => 'date'],
                    'updated_at' => ['type' => 'date']
                ]
            ],
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                'analysis' => [
                    'analyzer' => [
                        'default' => [
                            'type' => 'standard'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function ensureIndexExists(): void
    {
        if (!$this->esClient->indexExists(self::INDEX_NAME)) {
            $this->esClient->createIndex(self::INDEX_NAME, $this->getIndexMapping());
            $this->logger->info('Created Elasticsearch index: ' . self::INDEX_NAME);
        }
    }

    public function indexListing(Listing $listing): void
    {
        // Sadece active ve silinmemiş listingler indexlenir
        if ($listing->getStatus() !== 'active' || $listing->isDeleted()) {
            $this->removeListing($listing);
            return;
        }

        $document = $this->transformToDocument($listing);
        $this->esClient->index(self::INDEX_NAME, (string) $listing->getId(), $document);
        $this->logger->debug('Indexed listing: ' . $listing->getId());
    }

    public function removeListing(Listing $listing): void
    {
        $this->esClient->delete(self::INDEX_NAME, (string) $listing->getId());
        $this->logger->debug('Removed listing from index: ' . $listing->getId());
    }

    public function bulkIndex(array $listings): int
    {
        $this->ensureIndexExists();
        
        $bulkParams = [];
        $count = 0;

        foreach ($listings as $listing) {
            if ($listing->getStatus() !== 'active' || $listing->isDeleted()) {
                continue;
            }

            $bulkParams[] = [
                'index' => [
                    '_index' => self::INDEX_NAME,
                    '_id' => (string) $listing->getId()
                ]
            ];
            $bulkParams[] = $this->transformToDocument($listing);
            $count++;

            // Her 500 kayıtta bir bulk gönder
            if ($count % 500 === 0) {
                $this->esClient->bulk($bulkParams);
                $bulkParams = [];
            }
        }

        // Kalan kayıtları gönder
        if (!empty($bulkParams)) {
            $this->esClient->bulk($bulkParams);
        }

        return $count;
    }

    public function recreateIndex(): void
    {
        $this->esClient->deleteIndex(self::INDEX_NAME);
        $this->esClient->createIndex(self::INDEX_NAME, $this->getIndexMapping());
        $this->logger->info('Recreated Elasticsearch index: ' . self::INDEX_NAME);
    }

    private function transformToDocument(Listing $listing): array
    {
        // Transform images to array
        $images = [];
        foreach ($listing->getImages() as $image) {
            $images[] = [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'position' => $image->getPosition()
            ];
        }

        return [
            'id' => (string) $listing->getId(),
            'seller_id' => $listing->getSellerId(),
            'category_id' => $listing->getCategory()->getId(),
            'title' => $listing->getTitle(),
            'description' => $listing->getDescription(),
            'price' => (float) $listing->getPrice(),
            'currency' => $listing->getCurrency(),
            'status' => $listing->getStatus(),
            'location' => $listing->getLocation(),
            'images' => $images,
            'created_at' => $listing->getCreatedAt()->format('c'),
            'updated_at' => $listing->getUpdatedAt()->format('c')
        ];
    }
}

