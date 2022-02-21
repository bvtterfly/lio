<?php

namespace Bvtterfly\Lio;

use Illuminate\Support\Str;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class TempImage
{
    private function __construct(
        private string $filename,
        private TemporaryDirectory $temporaryDirectory
    ) {
    }

    /**
     * @param string $content
     * @param string|null $filename
     * @return TempImage
     */
    public static function make(string $content, ?string $filename = null): TempImage
    {
        $filename ??= Str::random();
        $temporaryDirectory = (new TemporaryDirectory(config('lio.temporary_directory')))
            ->force()
            ->create();
        file_put_contents($temporaryDirectory->path($filename), $content);

        return new TempImage($filename, $temporaryDirectory);
    }

    public function mime(): string
    {
        return mime_content_type($this->path());
    }

    public function path(): string
    {
        return $this->temporaryDirectory->path($this->filename);
    }

    public function extension(): string
    {
        $extension = pathinfo($this->path(), PATHINFO_EXTENSION);

        return strtolower($extension);
    }

    public function delete()
    {
        $this->temporaryDirectory->delete();
    }

    public function __destruct()
    {
        $this->delete();
    }
}
