<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageInterface
{
    /**
     * Upload a file and return its path and URL
     * @param UploadedFile $file
     * @param string $directory
     * @return array{path: string, url: string}
     */
    public function upload(UploadedFile $file, string $directory = ''): array;

    /**
     * Delete a file by its path
     * @param string $path
     * @return void
     */
    public function delete(string $path): void;

    /**
     * Get the public URL of a file
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string;
}

