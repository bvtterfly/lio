<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Image;
use Bvtterfly\Lio\Optimizer;

abstract class BaseOptimizer implements Optimizer
{
    protected string $imagePath = '';

    protected string $binaryPath = '';

    public string $binaryName = '';

    public function binaryName(): string
    {
        return $this->binaryName;
    }

    abstract public function canHandle(Image $image): bool;

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function setBinaryPath(string $binaryPath): static
    {
        if (strlen($binaryPath) > 0 && substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
            $binaryPath = $binaryPath.DIRECTORY_SEPARATOR;
        }

        $this->binaryPath = $binaryPath;

        return $this;
    }

    abstract public function getCommand(): string;
}
