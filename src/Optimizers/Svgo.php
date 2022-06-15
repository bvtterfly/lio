<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;

class Svgo extends WithArgumentsOptimizer
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
        return "\"{$this->getBinaryPath()}{$this->binaryName}\" {$this->getArgumentString()}"
            .' --input='.escapeshellarg($this->imagePath)
            .' --output='.escapeshellarg($this->imagePath);
    }
}
