<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\TempImage;

class Svgo extends BaseOptimizer
{
    public string $binaryName = 'svgo';

    public function canHandle(TempImage $image): bool
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
