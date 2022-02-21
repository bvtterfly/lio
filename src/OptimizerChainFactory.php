<?php

namespace Bvtterfly\Lio;

use Bvtterfly\Lio\Exceptions\InvalidConfiguration;
use Bvtterfly\Lio\Optimizers\BaseOptimizer;
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
        return (new OptimizerChain())
            ->useFilesystem(self::getFilesystem($config))
            ->useLogger(self::getLogger($config))
            ->setOptimizers(self::getOptimizers($config))
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

    private static function getOptimizers(array $config): array
    {
        return collect($config['optimizers'])
            ->mapWithKeys(function (array $options, string $optimizerClass) use ($config) {
                if (! is_a($optimizerClass, Optimizer::class, true)) {
                    throw InvalidConfiguration::notAnOptimizer($optimizerClass);
                }

                // Initialize optimizer class
                $newOptimizerClass = new $optimizerClass();

                if (
                    is_a($newOptimizerClass, BaseOptimizer::class, true) &&
                    self::getBinaryPath($config)
                ) {
                    $newOptimizerClass->setBinaryPath(self::getBinaryPath($config));
                }

                $newOptimizerClass->setOptions($options);

                return [$optimizerClass => $newOptimizerClass];
            })
            ->toArray();
    }

    private static function getBinaryPath(array $config): string
    {
        return $config['binary_dir_path'] ?? '';
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
