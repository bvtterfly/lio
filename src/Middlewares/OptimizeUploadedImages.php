<?php

namespace Bvtterfly\Lio\Middlewares;

use Bvtterfly\Lio\OptimizerChain;
use Closure;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OptimizeUploadedImages
{
    public function handle($request, Closure $next)
    {
        /** @var OptimizerChain $optimizerChain */
        $optimizerChain = app(OptimizerChain::class);

        collect($request->allFiles())
            ->flatten()
            ->filter(function (UploadedFile $file) {
                if (app()->environment('testing')) {
                    return true;
                }

                return $file->isValid();
            })
            ->each(function (UploadedFile $file) use ($optimizerChain) {
                $optimizerChain->optimizeLocal($file->getPathname());
            });

        return $next($request);
    }
}
