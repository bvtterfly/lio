<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Image;

class Jpegoptim extends BaseOptimizer
{
    public string $binaryName = 'jpegoptim';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/jpeg';
    }
}
