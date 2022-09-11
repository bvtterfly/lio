<?php

use Bvtterfly\Lio\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

uses(TestCase::class)->in(__DIR__);

function decreasedFilesystemFileSize(string $modifiedFilePath, string $originalFilePath)
{
    imageFileExists($originalFilePath);

    imageFileExists($modifiedFilePath);

    $originalFileSize = imageFileSize($originalFilePath);

    $modifiedFileSize = imageFileSize($modifiedFilePath);

    expect($modifiedFileSize)
        ->toBeGreaterThan(0)
        ->toBeLessThan($originalFileSize)
    ;
}

function getTempFilePath(string $filename): string
{
    return __DIR__.'/tempFiles/'.$filename;
}

function imageFileExists(string $path)
{
    expect(Storage::disk('images')->exists($path))->toBeTrue();
}

function imageFileSize(string $path): int
{
    return strlen(Storage::disk('images')->get($path));
}

function decreasedFileSize(string $modifiedFilePath, string $originalFilePath)
{
    expect(file_exists($modifiedFilePath))->toBeTrue();
    expect(file_exists($originalFilePath))->toBeTrue();

    $modifiedFileSize = filesize($modifiedFilePath);
    $originalFileSize = filesize($originalFilePath);

    expect($modifiedFileSize)->toBeLessThan($originalFileSize);
    expect($modifiedFileSize)->toBeGreaterThan(0);
}
