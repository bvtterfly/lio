<?php

use Bvtterfly\Lio\OptimizerChain;
use Illuminate\Support\Facades\Config;


it('gets a dummy logger', function (){
    Config::set('lio.log_optimizer_activity', false);
    $optimizerChain = \Bvtterfly\Lio\OptimizerChainFactory::create(Config::get('lio'));
    expect($optimizerChain->getLogger())
        ->toBeInstanceOf(\Bvtterfly\Lio\DummyLogger::class)
    ;
});

it('create a logger from class', function (){
    Config::set('lio.log_optimizer_activity', \Bvtterfly\Lio\DummyLogger::class);
    $optimizerChain = \Bvtterfly\Lio\OptimizerChainFactory::create(Config::get('lio'));
    expect($optimizerChain->getLogger())
        ->toBeInstanceOf(Psr\Log\LoggerInterface::class)
    ;
});

it('will throw an exception with a misconfigured optimizer', function (){
    Config::set('lio.optimizers', [stdClass::class => []]);
    app(OptimizerChain::class);
})->throws(\Bvtterfly\Lio\Exceptions\InvalidConfiguration::class);

it('will throw an exception with a misconfigured logger', function (){
    Config::set('lio.log_optimizer_activity', stdClass::class);
    app(OptimizerChain::class);
})->throws(\Bvtterfly\Lio\Exceptions\InvalidConfiguration::class);
