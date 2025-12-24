<?php

namespace App\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

class ElasticsearchClient
{
    private Client $client;

    public function __construct(
        private readonly string $elasticsearchUrl,
        private readonly LoggerInterface $logger,
        private readonly ?string $elasticsearchApiKey = null
    ) {
        $builder = ClientBuilder::create();
        
        // Parse URL - supports formats:
        // - http://localhost:9200 (local dev)
        // - https://user:pass@host:port (basic auth)
        // - https://host:port with API key
        $builder->setHosts([$elasticsearchUrl]);
        
        // If API key is provided (Elastic Cloud recommended)
        if ($this->elasticsearchApiKey) {
            $builder->setApiKey($this->elasticsearchApiKey);
        }
        
        // Enable SSL verification for HTTPS
        if (str_starts_with($elasticsearchUrl, 'https://')) {
            $builder->setSSLVerification(true);
        }
        
        $this->client = $builder->build();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function indexExists(string $index): bool
    {
        try {
            return $this->client->indices()->exists(['index' => $index])->asBool();
        } catch (\Exception $e) {
            $this->logger->error('ES indexExists error: ' . $e->getMessage());
            return false;
        }
    }

    public function createIndex(string $index, array $mapping): void
    {
        try {
            $this->client->indices()->create([
                'index' => $index,
                'body' => $mapping
            ]);
        } catch (\Exception $e) {
            $this->logger->error('ES createIndex error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteIndex(string $index): void
    {
        try {
            if ($this->indexExists($index)) {
                $this->client->indices()->delete(['index' => $index]);
            }
        } catch (\Exception $e) {
            $this->logger->error('ES deleteIndex error: ' . $e->getMessage());
        }
    }

    public function index(string $indexName, string $id, array $document): void
    {
        try {
            $this->client->index([
                'index' => $indexName,
                'id' => $id,
                'body' => $document
            ]);
        } catch (\Exception $e) {
            $this->logger->error('ES index error: ' . $e->getMessage());
        }
    }

    public function delete(string $indexName, string $id): void
    {
        try {
            $this->client->delete([
                'index' => $indexName,
                'id' => $id
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('ES delete error (may not exist): ' . $e->getMessage());
        }
    }

    public function search(string $indexName, array $query): array
    {
        try {
            $response = $this->client->search([
                'index' => $indexName,
                'body' => $query
            ]);
            return $response->asArray();
        } catch (\Exception $e) {
            $this->logger->error('ES search error: ' . $e->getMessage());
            return ['hits' => ['hits' => [], 'total' => ['value' => 0]]];
        }
    }

    public function bulk(array $params): void
    {
        try {
            $this->client->bulk(['body' => $params]);
        } catch (\Exception $e) {
            $this->logger->error('ES bulk error: ' . $e->getMessage());
        }
    }
}

