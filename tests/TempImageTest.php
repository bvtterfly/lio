<?php

use Bvtterfly\Lio\TempImage;
use Illuminate\Support\Facades\Config;

beforeEach(function(){
    $tempDirPath = __DIR__.'/temp';
    Config::set('lio.temporary_directory', $tempDirPath);
});

it('can create temp image', function () {
    $tempImage = TempImage::make('test', 'temp-filename.jpeg');
    expect(file_get_contents($tempImage->path()))->toBe('test');
    expect($tempImage->path())->toContain(__DIR__.'/temp');
});

it('can get type mime type', function () {
    $imageFile = __DIR__.'/tempFiles/image.jpeg';
    $tempImage = TempImage::make(file_get_contents($imageFile), 'temp-filename.jpeg');
    expect($tempImage->mime())->toBe('image/jpeg');
});

it('can get the extension', function () {
    $imageFile = __DIR__.'/tempFiles/image.jpeg';
    $tempImage = TempImage::make(file_get_contents($imageFile), 'temp-filename.jpeg');
    expect($tempImage->extension())->toBe('jpeg');
});

