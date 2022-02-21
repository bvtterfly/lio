<?php

namespace Bvtterfly\Lio\Exceptions;

use Bvtterfly\Lio\Optimizer;
use Exception;
use Psr\Log\LoggerInterface;

final class InvalidConfiguration extends Exception
{
    public static function notAnOptimizer(string $class)
    {
        $optimizerInterface = Optimizer::class;

        return new static("Configured optimizer `{$class}` does not implement `{$optimizerInterface}`.");
    }

    public static function notAnLogger(string $class)
    {
        $loggerInterface = LoggerInterface::class;

        return new static("Configured optimizer `{$class}` does not implement `{$loggerInterface}`.");
    }
}
