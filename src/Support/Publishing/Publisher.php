<?php

namespace Yxx\LaravelPlugin\Support\Publishing;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\PublisherInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Plugin;

abstract class Publisher implements PublisherInterface
{
    /**
     * @var Plugin
     */
    protected Plugin $plugin;

    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * @var Command
     */
    protected Command $console;

    /**
     * @var string
     */
    protected string $success;

    /**
     * @var string
     */
    protected string $error = '';

    /**
     * @var bool
     */
    protected bool $showMessage = true;

    /**
     * Publisher constructor.
     *
     * @param  Plugin  $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Show the result message.
     *
     * @return self
     */
    public function showMessage(): self
    {
        $this->showMessage = true;

        return $this;
    }

    /**
     * Hide the result message.
     *
     * @return self
     */
    public function hideMessage(): self
    {
        $this->showMessage = false;

        return $this;
    }

    /**
     * Get plugin instance.
     *
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * @param  RepositoryInterface  $repository
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * @param  Command  $console
     * @return $this
     */
    public function setConsole(Command $console): self
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Get console instance.
     *
     * @return Command
     */
    public function getConsole(): Command
    {
        return $this->console;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->repository->getFiles();
    }

    /**
     * Get destination path.
     *
     * @return string
     */
    abstract public function getDestinationPath(): string;

    /**
     * Get source path.
     *
     * @return string
     */
    abstract public function getSourcePath(): string;

    /**
     * Publish something.
     */
    public function publish(): void
    {
        if (! $this->console instanceof Command) {
            $message = "The 'console' property must instance of \\Illuminate\\Console\\Command.";

            throw new \RuntimeException($message);
        }

        if (! $this->getFilesystem()->isDirectory($sourcePath = $this->getSourcePath())) {
            return;
        }

        if (! $this->getFilesystem()->isDirectory($destinationPath = $this->getDestinationPath())) {
            $this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
        }

        if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath)) {
            if ($this->showMessage === true) {
                $this->console->line("<info>Published</info>: {$this->module->getStudlyName()}");
            }
        } else {
            $this->console->error($this->error);
        }
    }
}
