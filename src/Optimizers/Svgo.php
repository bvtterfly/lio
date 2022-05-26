<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Image;

class Svgo extends WithOptionsOptimizer
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
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString}"
            .' --input='.escapeshellarg($this->imagePath)
            .' --output='.escapeshellarg($this->imagePath);
    }
}
