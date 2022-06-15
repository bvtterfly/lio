<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\HasArguments;

abstract class WithArgumentsOptimizer extends CommandOptimizer implements HasArguments
{
    public array $arguments = [];

    final public function __construct(array $arguments = [])
    {
        $this->setArguments($arguments);
    }

    public function setArguments(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function getCommand(): string
    {
        return "\"{$this->getBinaryPath()}{$this->binaryName}\" {$this->getArgumentString()} ".escapeshellarg($this->imagePath);
    }

    protected function getArgumentString(): string
    {
        return implode(' ', $this->arguments);
    }
}
