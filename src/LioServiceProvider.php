<?php

namespace Bvtterfly\Lio;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LioServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lio')
            ->hasConfigFile()
        ;
    }

    public function packageRegistered()
    {
        $this->app->singleton(OptimizerChain::class, function () {
            return OptimizerChainFactory::create(config('lio'));
        });

        $this->app->alias(OptimizerChain::class, 'image-optimizer');
    }
}
