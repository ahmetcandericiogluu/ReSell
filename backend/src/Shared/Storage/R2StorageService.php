<?php

namespace App\Shared\Storage;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class R2StorageService implements StorageInterface
{
    private S3Client $s3Client;

    public function __construct(
        private readonly string $endpoint,
        private readonly string $region,
        private readonly string $bucket,
        private readonly string $accessKeyId,
        private readonly string $secretAccessKey,
        private readonly string $publicBaseUrl
    ) {
        $config = [
            'version' => 'latest',
            'region' => $this->region,
            'endpoint' => $this->endpoint,
            'credentials' => [
                'key' => $this->accessKeyId,
                'secret' => $this->secretAccessKey,
            ],
            'use_path_style_endpoint' => false,
        ];

        // TEMPORARY FIX: Disable SSL verification for local development
        // TODO: Remove this and configure proper CA bundle (see SSL_CERTIFICATE_FIX.md)
        if (getenv('APP_ENV') === 'dev' || ($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
            $config['http'] = [
                'verify' => false,
            ];
        }

        $this->s3Client = new S3Client($config);
    }

    public function upload(UploadedFile $file, string $directory = ''): array
    {
        // Generate unique filename
        $extension = $file->guessExtension();
        $filename = uniqid('', true) . '.' . $extension;

        // Build key (path in bucket)
        $key = $directory ? "{$directory}/{$filename}" : $filename;

        try {
            // Upload file to R2
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => fopen($file->getPathname(), 'rb'),
                'ContentType' => $file->getMimeType(),
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to upload file to R2: ' . $e->getMessage());
        }

        // Build URL
        $url = $this->publicBaseUrl . '/' . $key;

        return [
            'path' => $key,
            'url' => $url
        ];
    }

    public function delete(string $path): void
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to delete file from R2: ' . $e->getMessage());
        }
    }

    public function getUrl(string $path): string
    {
        return $this->publicBaseUrl . '/' . $path;
    }
}

