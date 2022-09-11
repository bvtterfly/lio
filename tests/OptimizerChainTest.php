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

it('can decrease size of a file', function ($filename, $tempFilename, $optimizerName, $doesntFindOptimizer) {
    $imageDist = Storage::disk('images');
    $filepath = getTempFilePath($tempFilename);
    $imageDist->put($filename, file_get_contents($filepath));
    /** @var OptimizerChain $optimizerChain */
    $optimizerChain = app(OptimizerChain::class);
    $optimizedFilename = "opt-{$filename}";
    $optimizerChain->optimize($filename, $optimizedFilename);
    decreasedFilesystemFileSize($optimizedFilename, $filename);
    $logger = $optimizerChain->getLogger();
    expect($logger)->toBeInstanceOf(ArrayLogger::class)
                   ->getAllLinesAsString()
                   ->toContain($optimizerName)
        ->not
        ->toContain($doesntFindOptimizer);
})->with([
    ['test.jpeg', 'image.jpeg', 'jpegoptim', 'gifsicle'],
    ['test.png', 'image.png', 'pngquant', 'jpegoptim'],
    ['test.gif', 'animated.gif', 'gifsicle', 'jpegoptim'],
    ['test.svg', 'graph.svg', 'svgo', 'jpegoptim'],
    ['test.webp', 'image.webp', 'cwebp', 'jpegoptim'],
]);

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
        ->toEqual($imageDist->get('opt-test.txt'));
});
