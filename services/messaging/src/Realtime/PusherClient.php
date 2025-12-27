<?php

namespace App\Realtime;

use Pusher\Pusher;
use Psr\Log\LoggerInterface;

/**
 * Wrapper service for Pusher SDK
 */
class PusherClient
{
    private ?Pusher $pusher = null;
    private bool $enabled = false;

    public function __construct(
        private readonly ?string $appId,
        private readonly ?string $key,
        private readonly ?string $secret,
        private readonly ?string $cluster,
        private readonly LoggerInterface $logger
    ) {
        $this->enabled = !empty($appId) && !empty($key) && !empty($secret) && !empty($cluster);
    }

    private function getPusher(): ?Pusher
    {
        if (!$this->enabled) {
            return null;
        }

        if ($this->pusher === null) {
            $this->pusher = new Pusher(
                $this->key,
                $this->secret,
                $this->appId,
                [
                    'cluster' => $this->cluster,
                    'useTLS' => true,
                ]
            );
        }

        return $this->pusher;
    }

    /**
     * Publish event to a channel
     */
    public function trigger(string $channel, string $event, array $data): bool
    {
        $pusher = $this->getPusher();
        
        if ($pusher === null) {
            $this->logger->info('Pusher disabled, skipping event', [
                'channel' => $channel,
                'event' => $event,
            ]);
            return false;
        }

        try {
            $pusher->trigger($channel, $event, $data);
            $this->logger->info('Pusher event sent', [
                'channel' => $channel,
                'event' => $event,
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Pusher trigger failed', [
                'channel' => $channel,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Authenticate a private channel subscription
     * 
     * @return string|null JSON auth response or null if not enabled
     */
    public function socketAuth(string $channel, string $socketId): ?string
    {
        $pusher = $this->getPusher();
        
        if ($pusher === null) {
            return null;
        }

        try {
            return $pusher->authorizeChannel($channel, $socketId);
        } catch (\Exception $e) {
            $this->logger->error('Pusher auth failed', [
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getCluster(): ?string
    {
        return $this->cluster;
    }
}

