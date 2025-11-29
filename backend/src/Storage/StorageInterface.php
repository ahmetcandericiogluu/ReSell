<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageInterface
{
    /**
     * Upload a file to storage
     * 
     * @param UploadedFile $file The file to upload
     * @param string $directory The directory path (e.g., 'listings/123')
     * @return array ['path' => string, 'url' => string]
     */
    public function upload(UploadedFile $file, string $directory = ''): array;

    /**
     * Delete a file from storage
     * 
     * @param string $path The file path to delete
     * @return void
     */
    public function delete(string $path): void;

    /**
     * Get the public URL for a file
     * 
     * @param string $path The file path
     * @return string The public URL
     */
    public function getUrl(string $path): string;
}

