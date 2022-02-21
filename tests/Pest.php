<?php

use Bvtterfly\Lio\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

uses(TestCase::class)->in(__DIR__);

function decreasedFileSize(string $modifiedFilePath, string $originalFilePath)
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

function imageFileExists(string $path)
{
    expect(Storage::disk('images')->exists($path))->toBeTrue();
}

function imageFileSize(string $path): int
{
    return strlen(Storage::disk('images')->get($path));
}
