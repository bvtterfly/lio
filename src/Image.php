<?php

namespace Bvtterfly\Lio;

interface Image
{
    public function mime(): string;

    public function path(): string;

    public function extension(): string;
}
