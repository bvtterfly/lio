<?php

namespace Bvtterfly\Lio\Optimizers;

use Bvtterfly\Lio\Contracts\Image;
use Bvtterfly\Lio\Contracts\Optimizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

abstract class CommandOptimizer implements Optimizer
{
    protected string $imagePath = '';

    protected ?LoggerInterface $logger = null;

    protected int $timeout = 60;

    public string $binaryName = '';

    public function binaryName(): string
    {
        return $this->binaryName;
    }

    public function setLogger(LoggerInterface $logger): Optimizer
    {
        $this->logger = $logger;

        return $this;
    }

    abstract public function canHandle(Image $image): bool;

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getBinaryPath(): string
    {
        $binaryPath = config("lio.binaries_path.{$this->binaryName}");
        if (strlen($binaryPath) > 0 && substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
            $binaryPath = $binaryPath.DIRECTORY_SEPARATOR;
        }

        return $binaryPath;
    }

    abstract public function getCommand(): string;

    public function run(): void
    {
        $command = $this->getCommand();

        $this->logger?->info("Executing `{$command}`");

        $process = Process::fromShellCommandline($command);

        $process
            ->setTimeout($this->timeout)
            ->run();

        $this->logResult($process);
    }

    protected function logResult(Process $process)
    {
        if (! $process->isSuccessful()) {
            $this->logger?->error("Process errored with `{$process->getErrorOutput()}`");

            return;
        }

        $this->logger?->info("Process successfully ended with output `{$process->getOutput()}`");
    }
}
