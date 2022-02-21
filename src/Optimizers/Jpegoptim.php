<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\TempImage;

class Jpegoptim extends BaseOptimizer
{
    public string $binaryName = 'jpegoptim';

    public function canHandle(TempImage $image): bool
    {
        return $image->mime() === 'image/jpeg';
    }
}
