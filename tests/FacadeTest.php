<?php

use Bvtterfly\Lio\Facades\ImageOptimizer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

it('has a facade', function (){
    $tempDirPath = __DIR__.'/temp';
    Storage::fake('images');
    Config::set('lio.disk', 'images');
    Config::set('lio.temporary_directory', $tempDirPath);
    $imageDist = Storage::disk('images');
    $imageDist->put('test.jpeg', file_get_contents(__DIR__.'/tempFiles/image.jpeg'));
    ImageOptimizer::optimize('test.jpeg', 'opt-test.jpeg');
    decreasedFileSize('opt-test.jpeg', 'test.jpeg');
});
