<?php

namespace Bvtterfly\Lio;

use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;

class Image
{

    private string $pathToImage;

    public function __construct(
        private Filesystem $disk,
        string             $pathToImage
    )
    {
        if (! $disk->exists($pathToImage)) {
            throw new InvalidArgumentException("`{$pathToImage}` does not exist");
        }
        $this->pathToImage = $pathToImage;
    }

    public static function make(Filesystem $filesystem, $pathToImage): Image
    {
        return new Image($filesystem, $pathToImage);
    }

    public function update(TempImage $tempImage, string $pathToOutput)
    {
        $this->disk->makeDirectory(dirname($pathToOutput));
        $this->disk->put($pathToOutput, file_get_contents($tempImage->path()));
    }

    public function tempImage(): TempImage
    {
        return TempImage::make(
            $this->disk->get($this->pathToImage),
            $this->pathToImage
        );
    }

}
