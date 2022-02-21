<?php

namespace Bvtterfly\Lio\Facades;

use Illuminate\Support\Facades\Facade;

class ImageOptimizer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'image-optimizer';
    }
}
