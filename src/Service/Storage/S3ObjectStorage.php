<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Storage;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class S3ObjectStorage implements ObjectStorageInterface
{
    private S3Client $client;

    public function __construct(
        #[Autowire('%env(S3_ENDPOINT)%')]
        private string $endpoint,
        #[Autowire('%env(S3_ACCESS_KEY)%')]
        private string $accessKey,
        #[Autowire('%env(S3_SECRET_KEY)%')]
        private string $secretKey,
        #[Autowire('%env(S3_BUCKET)%')]
        private string $bucket,
        #[Autowire('%env(S3_REGION)%')]
        private string $region,
    ) {
        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'endpoint' => $this->endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ],
        ]);
    }

    public function put(string $key, string $contents, string $mimeType): void
    {
        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $contents,
            'ContentType' => $mimeType,
        ]);
    }

    public function get(string $key): string
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        return (string) $result['Body'];
    }

    public function delete(string $key): void
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }

    public function exists(string $key): bool
    {
        try {
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return true;
        } catch (S3Exception) {
            return false;
        }
    }
}
