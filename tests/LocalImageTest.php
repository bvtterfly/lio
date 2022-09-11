<?php

use Bvtterfly\Lio\LocalImage;

it('can create a local image when file exist', function () {
    $imageFilePath = getTempFilePath('image.jpeg');
    $image = new LocalImage($imageFilePath);
    expect($image->path())->toEqual(__DIR__.'/tempFiles/image.jpeg');
});

it('cant create a local image when file doesnt exist', function () {
    $imageFilePath = getTempFilePath('test.png');
    new LocalImage($imageFilePath);
})->throws(InvalidArgumentException::class);

it('can get type mime type', function () {
    $imageFilePath = getTempFilePath('image.jpeg');
    $image = new LocalImage($imageFilePath);
    expect($image->mime())->toBe('image/jpeg');
});

it('can get the extension', function () {
    $imageFilePath = getTempFilePath('image.jpeg');
    $image = new LocalImage($imageFilePath);
    expect($image->extension())->toBe('jpeg');
});
