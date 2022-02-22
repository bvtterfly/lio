<?php


use Bvtterfly\Lio\Middlewares\OptimizeUploadedImages;
use Bvtterfly\Lio\Tests\TestEnvironmentMiddleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\UploadedFile;

it('will try to optimize all files in a request', function () {
    $tempDirectory = getTempDirectory();

    $originalImagePath = getImagePath('image.jpeg');

    $uploadPath = $tempDirectory->path('image.jpeg');

    copy($originalImagePath, $uploadPath);

    Route::middleware(OptimizeUploadedImages::class)->post('/', function () {
    });

    $this->call('POST', '/', [], [], ['upload' => getUploadFile($uploadPath)]);

    decreasedFileSize($uploadPath, $originalImagePath);

    $tempDirectory->delete();
});

it('will optimize all files at all depths', function (){

    $tempDirectory = getTempDirectory();

    $originalImagePath1 = getImagePath('image.jpeg');
    $uploadPath1 = $tempDirectory->path('image.jpeg');

    $originalImagePath2 = getImagePath('image.png');
    $uploadPath2 = $tempDirectory->path('image.png');

    $originalImagePath3 = getImagePath('graph.svg');
    $uploadPath3 = $tempDirectory->path('graph.svg');

    copy($originalImagePath1, $uploadPath1);
    copy($originalImagePath2, $uploadPath2);
    copy($originalImagePath3, $uploadPath3);

    Route::middleware(OptimizeUploadedImages::class)->post('/', function () {
    });

    $this->call('POST', '/', [], [], [
        'upload' => getUploadFile($uploadPath1),
        'one' => [
            'two' => getUploadFile($uploadPath2),
            'three' => [
                'four' => getUploadFile($uploadPath3),
            ],
        ],
    ]);

    decreasedFileSize($uploadPath1, $originalImagePath1);
    decreasedFileSize($uploadPath2, $originalImagePath2);
    decreasedFileSize($uploadPath3, $originalImagePath3);

    $tempDirectory->delete();

});

function getTempDirectory()
{
    return (new Spatie\TemporaryDirectory\TemporaryDirectory(__DIR__.'/temp/'))
        ->force()
        ->create()
    ;
}


function getImagePath($filename)
{
    return __DIR__.'/tempFiles/'.$filename;
}

function getUploadFile($path)
{
    return new UploadedFile(
        $path,
        pathinfo($path, PATHINFO_BASENAME),
        mime_content_type($path),
        filesize($path),
        true
    );
}
