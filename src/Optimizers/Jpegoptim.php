<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Image;

class Jpegoptim extends WithOptionsOptimizer
{
    public string $binaryName = 'jpegoptim';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/jpeg';
    }
}
