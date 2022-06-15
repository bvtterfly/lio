<?php

namespace Bvtterfly\Lio\Contracts;

use Psr\Log\LoggerInterface;

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
    public function setImagePath(string $imagePath): self;

    /**
     * Sets the logger for logging optimization process.
     *
     * @param  LoggerInterface  $logger
     *
     * @return Optimizer
     */
    public function setLogger(LoggerInterface $logger): self;

    /**
     * Sets the amount of seconds optimizer may use.
     *
     * @param  int  $timeout
     *
     * @return Optimizer
     */
    public function setTimeout(int $timeout): self;

    /**
     * Runs the optimizer.
     *
     * @return void
     */
    public function run(): void;
}
