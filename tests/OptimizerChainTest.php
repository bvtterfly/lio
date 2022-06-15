<?php

use Bvtterfly\Lio\OptimizerChain;
use Bvtterfly\Lio\Optimizers\ReSmushOptimizer;
use Bvtterfly\Lio\Tests\ArrayLogger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\TemporaryDirectory\TemporaryDirectory;

beforeEach(function () {
    $tempDirPath = __DIR__.'/temp';
    Storage::fake('images');
    Config::set('lio.disk', 'images');
    Config::set('lio.temporary_directory', $tempDirPath);
    Config::set('lio.log_optimizer_activity', ArrayLogger::class);
});

it('can optimize a filesystem image and decrease size of a jpeg file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.jpeg', file_get_contents(__DIR__.'/tempFiles/image.jpeg'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.jpeg', 'opt-test.jpeg');
    decreasedFilesystemFileSize('opt-test.jpeg', 'test.jpeg');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
        ->getAllLinesAsString()
        ->toContain('jpegoptim')
        ->getAllLinesAsString()
        ->not
        ->toContain('gifsicle')
    ;
});

it('can optimize a local image', function () {
    $tempDirectory = (new TemporaryDirectory(__DIR__.'/temp'))->force()->create();
    $imagePath = __DIR__.'/tempFiles/image.jpeg';
    $optimizedImagePath = $tempDirectory->path('image.jpeg');
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimizeLocal($imagePath, $optimizedImagePath);
    expect(file_exists($optimizedImagePath))->toBeTrue();
    $tempDirectory->delete();
});

it('can decrease size of a png file with resmush', function () {
    Config::set('lio.optimizers', [
        ReSmushOptimizer::class => [
            'quality' => 92,
            'retry' => 3,
            'mime' => [
                'image/png',
                'image/jpeg',
                'image/gif',
                'image/bmp',
                'image/tiff',
            ],

            'exif' => false,
        ],
    ]);
    $imageDist = Storage::disk('images');
    $imageDist->put('test.png', file_get_contents(__DIR__.'/tempFiles/image.png'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.png', 'opt-test.png');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
                   ->getAllLinesAsString()
                   ->toContain('reSmush')
        ->not
        ->toContain('jpegoptim');
});

it('can decrease size of a png file', function () {
    $imageDist = Storage::disk('images');
    $imageDist->put('test.png', file_get_contents(__DIR__.'/tempFiles/image.png'));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizerChain->optimize('test.png', 'opt-test.png');
    decreasedFilesystemFileSize('opt-test.png', 'test.png');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
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
    decreasedFilesystemFileSize('opt-test.gif', 'test.gif');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
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
    decreasedFilesystemFileSize('opt-test.svg', 'test.svg');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
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
    decreasedFilesystemFileSize('opt-test.webp', 'test.webp');
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
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
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
        ->getAllLines()
        ->toBeArray()
        ->toHaveCount(1)
        ->and($imageDist->get('test.txt'))
        ->toEqual($imageDist->get('opt-test.txt'))
    ;
});
