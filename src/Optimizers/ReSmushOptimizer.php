<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\HasConfig;
use Bvtterfly\Lio\Contracts\Image;
use Bvtterfly\Lio\Contracts\Optimizer;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;

class ReSmushOptimizer implements Optimizer, HasConfig
{
    public const ENDPOINT = 'api.resmush.it/ws.php';

    private const MAX_FILE_SIZE = 5242880;

    private const DEFAULT_QUALITY = 92;

    private const DEFAULT_EXIF = false;

    private const DEFAULT_RETRY = 3;

    private const SUPPORTED_MIMES = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/bmp',
        'image/tiff',
    ];

    protected string $imagePath = '';

    private array $config = [];

    protected ?LoggerInterface $logger = null;

    protected int $timeout = 60;

    public function __construct($configs = [])
    {
        $this->setConfig($configs);
    }

    public function canHandle(Image $image): bool
    {
        return in_array($image->mime(), $this->getMime()) && filesize($image->path()) < self::MAX_FILE_SIZE;
    }

    public function setImagePath(string $imagePath): Optimizer
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function setLogger(LoggerInterface $logger): Optimizer
    {
        $this->logger = $logger;

        return $this;
    }

    public function setTimeout(int $timeout): Optimizer
    {
        $this->timeout = $timeout;

        return $this;
    }

    private function upload(): ?Response
    {
        $file = fopen($this->imagePath, 'r');

        $params = http_build_query([
            'qlty' => $this->getQuality(),
            'exif' => $this->getExif(),
        ]);

        return rescue(
            fn () => Http::attach('files', $file, basename($this->imagePath))
                                   ->timeout($this->timeout)
                                   ->retry($this->getRetry())
                                   ->post(self::ENDPOINT.'?'.$params),
            fn ($e) => $e instanceof RequestException ? $e->response : null
        );
    }

    public function run(): void
    {
        $this->logger?->info('Uploading image to reSmush');

        $result = $this->upload();

        if (! $result?->successful()) {
            $this->logger?->error('Failed to upload image: '.$result->body());

            return;
        }

        $json = $result->json();

        $this->logger?->info('Downloading optimized image from reSmush');

        $destinationPath = Arr::get($json, 'dest');

        $downloadResponse = rescue(
            fn () => Http::timeout($this->timeout)->retry($this->getRetry())->get($destinationPath),
            fn ($e) => $e instanceof RequestException ? $e->response : null
        );

        if ($downloadResponse?->successful()) {
            file_put_contents($this->imagePath, $downloadResponse->body());
            $this->logger->info('Image Optimized successfully');
        } else {
            $this->logger->error('Failed to download image from: '.$destinationPath);
            $this->logger->error('Error: '.$downloadResponse?->body());
        }
    }

    public function setConfig(array $config = [])
    {
        $this->config = $config;
    }

    public function getQuality(): int
    {
        return Arr::get($this->config, 'quality') ?? self::DEFAULT_QUALITY;
    }

    public function getMime(): array
    {
        return Arr::get($this->config, 'mime') ?? self::SUPPORTED_MIMES;
    }

    public function getExif(): bool
    {
        return Arr::get($this->config, 'exif') ?? self::DEFAULT_EXIF;
    }

    public function getRetry(): int
    {
        return Arr::get($this->config, 'retry') ?? self::DEFAULT_RETRY;
    }
}
