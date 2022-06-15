<?php

namespace Bvtterfly\Lio;

use Bvtterfly\Lio\Contracts\HasArguments;
use Bvtterfly\Lio\Contracts\HasConfig;
use Bvtterfly\Lio\Contracts\Optimizer;
use Bvtterfly\Lio\Exceptions\InvalidConfiguration;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;

class OptimizerChainFactory
{
    /**
     * @throws InvalidConfiguration
     */
    public static function create(array $config = []): OptimizerChain
    {
        $logger = self::getLogger($config);

        return (new OptimizerChain())
            ->useFilesystem(self::getFilesystem($config))
            ->useLogger($logger)
            ->setOptimizers(self::getOptimizers($config, $logger))
            ->setTimeout($config['timeout']);
    }

    protected static function getLogger($config): LoggerInterface
    {
        $configuredLogger = $config['log_optimizer_activity'];

        /** @var LogManager $logManager */
        $logManager = app(LogManager::class);

        if ($configuredLogger === true) {
            return $logManager->channel();
        }

        if ($configuredLogger === false) {
            return new DummyLogger();
        }

        if (class_exists($configuredLogger)) {
            if (is_a($configuredLogger, LoggerInterface::class, true)) {
                return new $configuredLogger();
            }

            throw InvalidConfiguration::notAnLogger($configuredLogger);
        }

        return $logManager->channel($configuredLogger);
    }

    private static function getOptimizers(array $config, LoggerInterface $logger): array
    {
        return collect($config['optimizers'])
            ->map(function (mixed $value, mixed $key) use ($logger) {
                $options = [];
                if (is_int($key)) {
                    $optimizer = $value;
                } else {
                    $optimizer = $key;
                    if (is_array($value)) {
                        $options = $value;
                    }
                }
                if (
                    ! is_a($optimizer, Optimizer::class, true)
                ) {
                    $optimizerClass = is_object($optimizer) ? get_class($optimizer) : $optimizer;

                    throw InvalidConfiguration::notAnOptimizer($optimizerClass);
                }

                if (is_string($optimizer)) {
                    $optimizer = app()->make($optimizer);
                    if ($optimizer instanceof HasArguments && count($options)) {
                        $optimizer->setArguments($options);
                    }
                    if ($optimizer instanceof HasConfig && count($options)) {
                        $optimizer->setConfig($options);
                    }
                }

                /** @var Optimizer $optimizer */
                $optimizer->setLogger($logger);

                return $optimizer;
            })
            ->toArray();
    }

    private static function getFilesystem(array $config): Filesystem
    {
        $disk = $config['disk'];

        /** @var Factory $factory */
        $factory = app(Factory::class);

        if ($disk === 'default') {
            return $factory->disk();
        }

        return $factory->disk($disk);
    }
}
