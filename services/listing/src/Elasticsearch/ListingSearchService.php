<?php

namespace App\Elasticsearch;

use Psr\Log\LoggerInterface;

class ListingSearchService
{
    public function __construct(
        private readonly ElasticsearchClient $esClient,
        private readonly LoggerInterface $logger
    ) {
    }

    public function search(
        ?string $query = null,
        ?int $categoryId = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?string $location = null,
        string $sort = 'created_at',
        string $order = 'desc',
        int $page = 1,
        int $limit = 20
    ): array {
        $must = [];
        $filter = [];

        // Status ve deleted filtresi (her zaman active)
        $filter[] = ['term' => ['status' => 'active']];

        // Full-text arama
        if ($query) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['title^2', 'description'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO'
                ]
            ];
        }

        // Kategori filtresi
        if ($categoryId) {
            $filter[] = ['term' => ['category_id' => $categoryId]];
        }

        // Fiyat aralığı
        if ($minPrice !== null || $maxPrice !== null) {
            $range = [];
            if ($minPrice !== null) {
                $range['gte'] = $minPrice;
            }
            if ($maxPrice !== null) {
                $range['lte'] = $maxPrice;
            }
            $filter[] = ['range' => ['price' => $range]];
        }

        // Lokasyon filtresi
        if ($location) {
            $must[] = [
                'match' => [
                    'location' => [
                        'query' => $location,
                        'fuzziness' => 'AUTO'
                    ]
                ]
            ];
        }

        // Sort field kontrolü
        $sortField = match ($sort) {
            'price' => 'price',
            'created_at' => 'created_at',
            default => 'created_at'
        };

        $esQuery = [
            'query' => [
                'bool' => [
                    'must' => $must ?: [['match_all' => (object)[]]],
                    'filter' => $filter
                ]
            ],
            'sort' => [
                [$sortField => ['order' => $order === 'asc' ? 'asc' : 'desc']]
            ],
            'from' => ($page - 1) * $limit,
            'size' => $limit,
            '_source' => true
        ];

        $this->logger->debug('ES Query: ' . json_encode($esQuery));

        $response = $this->esClient->search(ListingIndexer::INDEX_NAME, $esQuery);

        $hits = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;

        $results = array_map(fn($hit) => $hit['_source'], $hits);

        return [
            'data' => $results,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit)
            ]
        ];
    }
}

