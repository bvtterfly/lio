<?php

namespace Bvtterfly\Lio\Optimizers;

abstract class WithOptionsOptimizer extends BaseOptimizer
{
    public array $options = [];

    final public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public static function withOptions(array $options = []): static
    {
        return new static($options);
    }

    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString} ".escapeshellarg($this->imagePath);
    }
}
