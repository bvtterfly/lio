<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Optimizer;

abstract class BaseOptimizer implements Optimizer
{
    public array $options = [];

    public string $imagePath = '';

    public string $binaryPath = '';

    public string $binaryName = '';

    public function __construct($options = [])
    {
        $this->setOptions($options);
    }

    public function binaryName(): string
    {
        return $this->binaryName;
    }

    public function setBinaryPath(string $binaryPath)
    {
        if (strlen($binaryPath) > 0 && substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
            $binaryPath = $binaryPath.DIRECTORY_SEPARATOR;
        }

        $this->binaryPath = $binaryPath;

        return $this;
    }

    public function setImagePath(string $imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString} ".escapeshellarg($this->imagePath);
    }
}
