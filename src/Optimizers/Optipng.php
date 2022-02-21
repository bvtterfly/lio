<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\TempImage;

class Optipng extends BaseOptimizer
{
    public string $binaryName = 'optipng';

    public function canHandle(TempImage $image): bool
    {
        return $image->mime() === 'image/png';
    }
}
