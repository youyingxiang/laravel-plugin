<?php

namespace Yxx\LaravelPlugin\Support;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\DecompressPluginException;

class DecompressPlugin
{
    /**
     * @var string
     */
    protected string $compressPath;

    protected RepositoryInterface $repository;

    protected string $tmpDecompressPath;

    protected Filesystem $filesystem;

    public function __construct(string $compressPath)
    {
        $this->compressPath = $compressPath;
        $this->repository = app('plugins.repository');
        $this->tmpDecompressPath = dirname($this->compressPath).'/.tmp';
        $this->filesystem = app('files');
    }

    public function handle(): ?string
    {
        $archive = new \ZipArchive();

        $op = $archive->open($this->compressPath);

        if ($op !== true) {
            return null;
        }

        $archive->extractTo($this->tmpDecompressPath);

        $archive->close();

        $this->filesystem->moveDirectory($this->tmpDecompressPath, $decompressPath = $this->getDecompressPath(), true);

        return basename($decompressPath);
    }

    public function getDecompressPath(): string
    {
        if (! $this->filesystem->exists("{$this->tmpDecompressPath}/plugin.json")) {
            throw new DecompressPluginException('Plugin parsing error.');
        }

        $plugName = Json::make("{$this->tmpDecompressPath}/plugin.json")->get('name');

        $decompressPath = $this->repository->getPluginPath($plugName);

        if (! $this->filesystem->isDirectory($decompressPath)) {
            $this->filesystem->makeDirectory($decompressPath, 0775, true);
        }

        return $decompressPath;
    }

    public function __destruct()
    {
        if ($this->filesystem->isDirectory($this->tmpDecompressPath)) {
            $this->filesystem->delete($this->tmpDecompressPath);
        }
    }
}
