<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Image;

class Svgo2 extends BaseOptimizer
{
    public string $binaryName = 'svgo';

    public string $configPath = '';

    public function __construct(string $configPath = null)
    {
        $this->configPath = $configPath ?? $this->getDefaultConfigPath();
    }

    public static function make(string $configPath = null)
    {
        return new self($configPath);
    }

    public function canHandle(Image $image): bool
    {
        if ($image->extension() !== 'svg') {
            return false;
        }

        return in_array($image->mime(), [
            'text/html',
            'image/svg',
            'image/svg+xml',
            'text/plain',
        ]);
    }

    public function getCommand(): string
    {
        return "\"{$this->binaryPath}{$this->binaryName}\""
            .' --config '.escapeshellarg($this->configPath)
            .' '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }

    private function getDefaultConfigPath()
    {
        return join(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            'resources',
            'js',
            'svgo.config.js',
        ]);
    }
}
