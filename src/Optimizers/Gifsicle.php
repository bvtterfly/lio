<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;

class Gifsicle extends WithArgumentsOptimizer
{
    public string $binaryName = 'gifsicle';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/gif';
    }

    public function getCommand(): string
    {
        return "\"{$this->getBinaryPath()}{$this->binaryName}\" {$this->getArgumentString()}"
            .' -i '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }
}
