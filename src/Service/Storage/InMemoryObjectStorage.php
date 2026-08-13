<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Storage;

final class InMemoryObjectStorage implements ObjectStorageInterface
{
    /** @var array<string, array{contents: string, mimeType: string}> */
    private array $objects = [];

    public function put(string $key, string $contents, string $mimeType): void
    {
        $this->objects[$key] = [
            'contents' => $contents,
            'mimeType' => $mimeType,
        ];
    }

    public function get(string $key): string
    {
        if (!isset($this->objects[$key])) {
            throw new \RuntimeException(sprintf('Object "%s" not found.', $key));
        }

        return $this->objects[$key]['contents'];
    }

    public function delete(string $key): void
    {
        unset($this->objects[$key]);
    }

    public function exists(string $key): bool
    {
        return isset($this->objects[$key]);
    }
}
