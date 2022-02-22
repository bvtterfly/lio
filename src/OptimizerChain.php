<?php

namespace Bvtterfly\Lio;

use Illuminate\Contracts\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class OptimizerChain
{
    /* @var Optimizer[] */
    private array $optimizers = [];

    private LoggerInterface $logger;

    private int $timeout = 60;

    private Filesystem $filesystem;

    public function __construct()
    {
    }

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
        $image = FilesystemImage::make($this->filesystem, $pathToImage);

        $tempImage = $image->tempImage();

        $pathToImage = $tempImage->path();

        $this->optimizeImage($pathToImage, $tempImage);

        $image->update($tempImage, $pathToOutput);

        $tempImage->delete();
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
            $this->applyOptimizer($optimizer, $image);
        }
    }

    protected function applyOptimizer(Optimizer $optimizer, Image $image)
    {
        if (! $optimizer->canHandle($image)) {
            return;
        }

        $optimizerClass = get_class($optimizer);

        $this->logger->info("Using optimizer: `{$optimizerClass}`");

        $optimizer->setImagePath($image->path());

        $command = $optimizer->getCommand();

        $this->logger->info("Executing `{$command}`");

        $process = Process::fromShellCommandline($command);

        $process
            ->setTimeout($this->timeout)
            ->run();

        $this->logResult($process);
    }

    protected function logResult(Process $process)
    {
        if (! $process->isSuccessful()) {
            $this->logger->error("Process errored with `{$process->getErrorOutput()}`");

            return;
        }

        $this->logger->info("Process successfully ended with output `{$process->getOutput()}`");
    }
}
