<?php

use Bvtterfly\Lio\Image;
use Bvtterfly\Lio\TempImage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $tempDirPath = __DIR__.'/temp';
    Storage::fake('images');
    Config::set('lio.disk', 'images');
    Config::set('lio.temporary_directory', $tempDirPath);
});

it('throw an exception when given a non existing file', function () {
    new Image(Storage::disk('images'), 'non existing file');
})->throws(InvalidArgumentException::class, "`non existing file` does not exist");

it('can get a temp file', function () {
    $imagesDisk = Storage::disk('images');
    $imagesDisk->put('test-file.txt', 'content');
    $image = new Image(Storage::disk('images'), 'test-file.txt');
    $tempImage = $image->tempImage();
    expect($tempImage)->toBeInstanceOf(TempImage::class);
});

it('can update file content from temp file', function () {
    $imagesDisk = Storage::disk('images');
    $imagesDisk->put('test-file.txt', 'content');
    $image = new Image(Storage::disk('images'), 'test-file.txt');
    $tempImage = $image->tempImage();
    file_put_contents($tempImage->path(), 'new content');
    $image->update($tempImage, 'new-test-file.txt');
    expect($imagesDisk)
        ->exists('new-test-file.txt')
        ->toBeTrue()
        ->get('new-test-file.txt')
        ->toBe('new content')
    ;
});
