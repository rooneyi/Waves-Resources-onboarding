<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Storage;

interface ObjectStorageInterface
{
    public function put(string $key, string $contents, string $mimeType): void;

    public function get(string $key): string;

    public function delete(string $key): void;

    public function exists(string $key): bool;
}
