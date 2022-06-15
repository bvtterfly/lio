<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;
use Illuminate\Support\Arr;

class Svgo2 extends WithConfigOptimizer
{
    public string $binaryName = 'svgo';

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
        return "\"{$this->getBinaryPath()}{$this->binaryName}\""
            .' --config '.escapeshellarg($this->getConfigPath())
            .' '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }

    private function getConfigPath()
    {
        return Arr::get($this->config, 'path') ?? $this->getDefaultConfigPath();
    }

    private function getDefaultConfigPath(): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            'resources',
            'js',
            'svgo.config.js',
        ]);
    }
}
