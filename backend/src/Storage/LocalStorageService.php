<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LocalStorageService implements StorageInterface
{
    public function __construct(
        private readonly string $uploadBasePath,
        private readonly string $publicBaseUrl
    ) {
    }

    public function upload(UploadedFile $file, string $directory = ''): array
    {
        // Create target directory if it doesn't exist
        $targetDirectory = $this->uploadBasePath . '/' . $directory;
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        // Generate unique filename
        $extension = $file->guessExtension();
        $filename = uniqid('', true) . '.' . $extension;

        try {
            // Move file to target directory
            $file->move($targetDirectory, $filename);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage());
        }

        // Build path and URL
        $path = $directory ? "{$directory}/{$filename}" : $filename;
        $url = $this->publicBaseUrl . '/' . $path;

        return [
            'path' => $path,
            'url' => $url
        ];
    }

    public function delete(string $path): void
    {
        $fullPath = $this->uploadBasePath . '/' . $path;
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function getUrl(string $path): string
    {
        return $this->publicBaseUrl . '/' . $path;
    }
}

