<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\TempImage;

class Gifsicle extends BaseOptimizer
{
    public string $binaryName = 'gifsicle';

    public function canHandle(TempImage $image): bool
    {
        return $image->mime() === 'image/gif';
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString}"
            .' -i '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }
}
