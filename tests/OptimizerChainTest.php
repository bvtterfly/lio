<?php

use Bvtterfly\Lio\OptimizerChain;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $tempDirPath = __DIR__.'/temp';
    Storage::fake('images');
    Config::set('lio.disk', 'images');
    Config::set('lio.temporary_directory', $tempDirPath);
    Config::set('lio.log_optimizer_activity', \Bvtterfly\Lio\Tests\ArrayLogger::class);
});

it('can decrease size of a jpeg file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.jpeg', file_get_contents(__DIR__.'/tempFiles/image.jpeg'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.jpeg', 'opt-test.jpeg');
    decreasedFileSize('opt-test.jpeg', 'test.jpeg');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(\Bvtterfly\Lio\Tests\ArrayLogger::class)
        ->getAllLinesAsString()
        ->toContain('jpegoptim')
        ->getAllLinesAsString()
        ->not
        ->toContain('gifsicle')
    ;
});

it('can decrease size of a png file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.png', file_get_contents(__DIR__.'/tempFiles/image.png'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.png', 'opt-test.png');
    decreasedFileSize('opt-test.png', 'test.png');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(\Bvtterfly\Lio\Tests\ArrayLogger::class)
        ->getAllLinesAsString()
        ->toContain('pngquant')
        ->not
        ->toContain('jpegoptim')
    ;
});

it('can decrease size of a gif file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.gif', file_get_contents(__DIR__.'/tempFiles/animated.gif'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.gif', 'opt-test.gif');
    decreasedFileSize('opt-test.gif', 'test.gif');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(\Bvtterfly\Lio\Tests\ArrayLogger::class)
        ->getAllLinesAsString()
        ->toContain('gifsicle')
        ->not
        ->toContain('jpegoptim')
    ;
});


it('can decrease size of a svg file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.svg', file_get_contents(__DIR__.'/tempFiles/graph.svg'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.svg', 'opt-test.svg');
    decreasedFileSize('opt-test.svg', 'test.svg');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(\Bvtterfly\Lio\Tests\ArrayLogger::class)
        ->getAllLinesAsString()
        ->toContain('svgo')
        ->not
        ->toContain('jpegoptim')
    ;
});

it('can decrease size of a webp file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.webp', file_get_contents(__DIR__.'/tempFiles/image.webp'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.webp', 'opt-test.webp');
    decreasedFileSize('opt-test.webp', 'test.webp');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(\Bvtterfly\Lio\Tests\ArrayLogger::class)
        ->getAllLinesAsString()
        ->toContain('cwebp')
        ->not
        ->toContain('jpegoptim')
    ;
});

it('cant optimize text file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.txt', 'test');
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.txt', 'opt-test.txt');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(\Bvtterfly\Lio\Tests\ArrayLogger::class)
        ->getAllLines()
        ->toBeArray()
        ->toHaveCount(1)
        ->and($imageDist->get('test.txt'))
        ->toEqual($imageDist->get('opt-test.txt'))
    ;
});
