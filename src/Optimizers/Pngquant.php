<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Image;

class Pngquant extends WithOptionsOptimizer
{
    public string $binaryName = 'pngquant';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/png';
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString}"
            .' '.escapeshellarg($this->imagePath)
            .' --output='.escapeshellarg($this->imagePath);
    }
}
