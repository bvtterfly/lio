<?php

namespace Bvtterfly\Lio;

interface Optimizer
{
    /**
     * Determines if the given image can be handled by the optimizer.
     *
     * @param Image $image
     *
     * @return bool
     */
    public function canHandle(Image $image): bool;

    /**
     * Sets the path to the image that should be optimized.
     *
     * @param string $imagePath
     *
     * @return Optimizer
     */
    public function setImagePath(string $imagePath): Optimizer;

    /**
     * Gets the command that should be executed.
     *
     * @return string
     */
    public function getCommand(): string;
}
