<?php

namespace Bvtterfly\Lio;

use Bvtterfly\Lio\Contracts\HasArguments;
use Bvtterfly\Lio\Contracts\HasConfig;
use Bvtterfly\Lio\Contracts\Optimizer;
use Bvtterfly\Lio\Exceptions\InvalidConfiguration;
use Illuminate\Contracts\Container\BindingResolutionException;
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

    /**
     * @throws InvalidConfiguration
     */
    protected static function getLogger($config): LoggerInterface
    {
        $configuredLogger = $config['log_optimizer_activity'];

        if (class_exists($configuredLogger)) {
            self::ensureLogger($configuredLogger);

            return new $configuredLogger();
        }

        /** @var LogManager $logManager */
        $logManager = app(LogManager::class);

        if (is_bool($configuredLogger)) {
            $configuredLogger = $configuredLogger ? $logManager->getDefaultDriver() : 'null';
        }

        return $logManager->channel($configuredLogger);
    }

    private static function getOptimizers(array $config, LoggerInterface $logger): array
    {
        return collect($config['optimizers'])
            ->map(function (mixed $value, mixed $key) use ($logger) {
                [$optimizer, $options] = self::getOptimizerAndOptions($key, $value);

                self::ensureOptimizer($optimizer);


                if (is_string($optimizer)) {
                    $optimizer = self::createOptimizer($optimizer, $options);
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

    /**
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return array
     */
    private static function getOptimizerAndOptions(mixed $key, mixed $value): array
    {
        $options = [];
        if (is_int($key)) {
            return [$value, $options];
        }

        $optimizer = $key;

        if (is_array($value)) {
            $options = $value;
        }

        return [$optimizer, $options];
    }

    /**
     * @throws InvalidConfiguration
     */
    private static function ensureOptimizer(mixed $optimizer): void
    {
        if (
            ! is_a($optimizer, Optimizer::class, true)
        ) {
            $optimizerClass = is_object($optimizer) ? get_class($optimizer) : $optimizer;

            throw InvalidConfiguration::notAnOptimizer($optimizerClass);
        }
    }

    /**
     * @throws InvalidConfiguration
     */
    private static function ensureLogger(string $logger): void
    {
        if (! is_a($logger, LoggerInterface::class, true)) {
            throw InvalidConfiguration::notAnLogger($logger);
        }
    }

    /**
     * @param  class-string<Optimizer>  $optimizerClass
     * @param  array  $options
     *
     * @return Optimizer
     * @throws BindingResolutionException
     */
    private static function createOptimizer(string $optimizerClass, array $options): Optimizer
    {
        $optimizer = app()->make($optimizerClass);
        if ($optimizer instanceof HasArguments && count($options)) {
            $optimizer->setArguments($options);
        }
        if ($optimizer instanceof HasConfig && count($options)) {
            $optimizer->setConfig($options);
        }

        return $optimizer;
    }
}
