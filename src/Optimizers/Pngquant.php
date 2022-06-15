<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;

class Pngquant extends WithArgumentsOptimizer
{
    public string $binaryName = 'pngquant';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/png';
    }

    public function getCommand(): string
    {
        return "\"{$this->getBinaryPath()}{$this->binaryName}\" {$this->getArgumentString()}"
            .' '.escapeshellarg($this->imagePath)
            .' --output='.escapeshellarg($this->imagePath);
    }
}
