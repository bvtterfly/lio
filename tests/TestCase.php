<?php

namespace Bvtterfly\Lio\Tests;

use Bvtterfly\Lio\LioServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LioServiceProvider::class,
        ];
    }
}
