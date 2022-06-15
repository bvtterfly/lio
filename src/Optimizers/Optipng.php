<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;

class Optipng extends WithArgumentsOptimizer
{
    public string $binaryName = 'optipng';

    public function canHandle(Image $image): bool
    {
        return $image->mime() === 'image/png';
    }
}
