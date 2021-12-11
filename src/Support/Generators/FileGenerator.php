<?php

namespace Yxx\LaravelPlugin\Support\Generators;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Abstracts\Generator;
use Yxx\LaravelPlugin\Exceptions\FileAlreadyExistException;

class FileGenerator extends Generator
{
    /**
     * The path wil be used.
     *
     * @var string
     */
    protected string $path;

    /**
     * The contens will be used.
     *
     * @var string
     */
    protected string $contents;

    /**
     * The laravel filesystem or null.
     *
     * @var Filesystem
     */
    protected Filesystem $filesystem;
    /**
     * @var bool
     */
    private bool $overwriteFile;

    /**
     * The constructor.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  Filesystem|null  $filesystem
     */
    public function __construct(string $path, string $contents, ?Filesystem $filesystem = null)
    {
        $this->path = $path;
        $this->contents = $contents;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * Get contents.
     *
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * Set contents.
     *
     * @param string $contents
     *
     * @return FileGenerator
     */
    public function setContents(string $contents): FileGenerator
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * Get filesystem.
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set filesystem.
     *
     * @param Filesystem $filesystem
     *
     * @return FileGenerator
     */
    public function setFilesystem(Filesystem $filesystem): FileGenerator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return FileGenerator
     */
    public function setPath(string $path): FileGenerator
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param  bool  $overwrite
     * @return $this
     */
    public function withFileOverwrite(bool $overwrite): FileGenerator
    {
        $this->overwriteFile = $overwrite;

        return $this;
    }

    /**
     * @return bool
     * @throws FileAlreadyExistException
     */
    public function generate(): bool
    {
        $path = $this->getPath();
        if (! $this->filesystem->exists($path)) {
            return $this->filesystem->put($path, $this->getContents());
        }
        if ($this->overwriteFile === true) {
            return $this->filesystem->put($path, $this->getContents());
        }

        throw new FileAlreadyExistException('File already exists!');
    }
}
