<?php

namespace App\Contracts;

interface StorageService
{
    /**
     * Get content and metadata of a file.
     *
     * @return string[]
     */
    public function download(string $container, string $fileName): array;

    /**
     * Upload a file to the storage, overwrite the existing file.
     */
    public function upload(string $container, string $source, string $fileKeyOrPath, string $fileName): ?string;

    /**
     * Delete a file from the storage.
     */
    public function delete(string $container, string $fileName): void;
}
