<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;

class Cwebp extends WithArgumentsOptimizer
{
    public string $binaryName = 'cwebp';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/webp';
    }

    public function getCommand(): string
    {
        return "\"{$this->getBinaryPath()}{$this->binaryName}\" {$this->getArgumentString()}"
            .' '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }
}
