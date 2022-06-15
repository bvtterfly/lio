<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\HasConfig;

abstract class WithConfigOptimizer extends CommandOptimizer implements HasConfig
{
    public array $config = [];

    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    public function setConfig(array $config = [])
    {
        $this->config = $config;
    }
}
