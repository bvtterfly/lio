<?php

namespace Bvtterfly\Lio;

use InvalidArgumentException;

class LocalImage implements Image
{
    public function __construct(protected string $pathToImage)
    {
        if (! file_exists($pathToImage)) {
            throw new InvalidArgumentException("`{$pathToImage}` does not exist");
        }
    }

    public function mime(): string
    {
        return mime_content_type($this->pathToImage);
    }

    public function path(): string
    {
        return $this->pathToImage;
    }

    public function extension(): string
    {
        $extension = pathinfo($this->pathToImage, PATHINFO_EXTENSION);

        return strtolower($extension);
    }
}
