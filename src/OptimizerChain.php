<?php

namespace Bvtterfly\Lio;

use Bvtterfly\Lio\Contracts\Image;
use Bvtterfly\Lio\Contracts\Optimizer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

class OptimizerChain
{
    /* @var Optimizer[] */
    private array $optimizers = [];

    private LoggerInterface $logger;

    private int $timeout = 60;

    private Filesystem $filesystem;

    public function getOptimizers(): array
    {
        return $this->optimizers;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    public function addOptimizer(Optimizer $optimizer): static
    {
        $this->optimizers[] = $optimizer;

        return $this;
    }

    public function setOptimizers(array $optimizers): static
    {
        $this->optimizers = [];

        foreach ($optimizers as $optimizer) {
            $this->addOptimizer($optimizer);
        }

        return $this;
    }

    /** Sets the amount of seconds each separate optimizer may use.
     * @param int $timeoutInSeconds
     * @return $this
     */
    public function setTimeout(int $timeoutInSeconds): static
    {
        $this->timeout = $timeoutInSeconds;

        return $this;
    }

    public function useLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    public function useFilesystem(Filesystem $filesystem): static
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    public function optimize(string $pathToImage, string $pathToOutput)
    {
        $fileSystemImagePath = $pathToImage;

        $image = FilesystemImage::make($this->filesystem, $fileSystemImagePath);

        $tempImage = $image->tempImage();

        $pathToImage = $tempImage->path();

        try {
            $this->optimizeImage($pathToImage, $tempImage);

            $image->update($tempImage, $pathToOutput);
        } catch (\Exception $e) {
            $this->logger->error("Optimizing {$fileSystemImagePath} failed!");
            throw $e;
        } finally {
            $tempImage->delete();
        }
    }

    public function optimizeLocal(string $pathToImage, string $pathToOutput = null)
    {
        if ($pathToOutput) {
            copy($pathToImage, $pathToOutput);

            $pathToImage = $pathToOutput;
        }

        $image = new LocalImage($pathToImage);

        $this->optimizeImage($pathToImage, $image);
    }

    /**
     * @param string $pathToImage
     * @param Image $image
     * @return void
     */
    protected function optimizeImage(string $pathToImage, Image $image): void
    {
        $this->logger->info("Start optimizing {$pathToImage}");

        foreach ($this->optimizers as $optimizer) {
            $this->runOptimizer($optimizer, $image);
        }
    }

    protected function runOptimizer(Optimizer $optimizer, Image $image)
    {
        if (! $optimizer->canHandle($image)) {
            return;
        }

        $optimizerClass = get_class($optimizer);

        $this->logger->info("Using optimizer: `{$optimizerClass}`");

        $optimizer->setImagePath($image->path());
        $optimizer->setTimeout($this->timeout);
        $optimizer->setLogger($this->logger);

        $optimizer->run();
    }
}
