<?php

namespace Bvtterfly\Lio;

use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;

class FilesystemImage
{
    private string $pathToImage;

    public function __construct(
        private Filesystem $disk,
        string $pathToImage
    ) {
        if (! $disk->exists($pathToImage)) {
            throw new InvalidArgumentException("`{$pathToImage}` does not exist");
        }
        $this->pathToImage = $pathToImage;
    }

    public static function make(Filesystem $filesystem, $pathToImage): FilesystemImage
    {
        return new FilesystemImage($filesystem, $pathToImage);
    }

    public function update(TempLocalImage $tempImage, string $pathToOutput)
    {
        $this->disk->makeDirectory(dirname($pathToOutput));
        $this->disk->put($pathToOutput, file_get_contents($tempImage->path()));
    }

    public function tempImage(): TempLocalImage
    {
        return TempLocalImage::make(
            $this->disk->get($this->pathToImage),
            $this->pathToImage
        );
    }
}
