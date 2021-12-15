<?php

namespace Yxx\LaravelPlugin\Support\Process;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;

class Installer
{
    /**
     * The plugin name.
     *
     * @var string
     */
    protected string $name;

    /**
     * The version of plugin being installed.
     *
     * @var string|null
     */
    protected ?string $version;

    /**
     * The plugin repository instance.
     *
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * The console command instance.
     *
     * @var Command
     */
    protected Command $console;

    /**
     * The destionation path.
     *
     * @var string
     */
    protected string $path;

    /**
     * The process timeout.
     *
     * @var int
     */
    protected int $timeout = 3360;

    /**
     * @var null|string
     */
    private ?string $type;

    /**
     * @var bool
     */
    private bool $tree;

    /**
     * The constructor.
     *
     * @param  string  $name
     * @param  string|null  $version
     * @param  string|null  $type
     * @param  bool  $tree
     */
    public function __construct(string $name, string $version = null, string $type = null, bool $tree = false)
    {
        $this->name = $name;
        $this->version = $version;
        $this->type = $type;
        $this->tree = $tree;
    }

    /**
     * Set destination path.
     *
     * @param  string  $path
     * @return $this
     */
    public function setPath(string $path): Installer
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the plugin repository instance.
     *
     * @param  RepositoryInterface  $repository
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository): Installer
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set console command instance.
     *
     * @param  Command  $console
     * @return $this
     */
    public function setConsole(Command $console): Installer
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Set process timeout.
     *
     * @param  int  $timeout
     * @return $this
     */
    public function setTimeout(int $timeout): Installer
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Run the installation process.
     *
     * @return Process
     */
    public function run(): Process
    {
        $process = $this->getProcess();

        $process->setTimeout($this->timeout);

        if ($this->console instanceof Command) {
            $process->run(function ($type, $line) {
                $this->console->line($line);
            });
        }

        return $process;
    }

    /**
     * Get process instance.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        if ($this->type) {
            if ($this->tree) {
                return $this->installViaSubtree();
            }

            return $this->installViaGit();
        }

        return $this->installViaComposer();
    }

    /**
     * Get destination path.
     *
     * @return string
     */
    public function getDestinationPath(): string
    {
        if ($this->path) {
            return $this->path;
        }

        return $this->repository->getPluginPath($this->getPluginName());
    }

    /**
     * Get git repo url.
     *
     * @return string|null
     */
    public function getRepoUrl(): ?string
    {
        switch ($this->type) {
            case 'github':
                return "git@github.com:{$this->name}.git";

            case 'github-https':
                return "https://github.com/{$this->name}.git";

            case 'gitlab':
                return "git@gitlab.com:{$this->name}.git";
                break;

            case 'bitbucket':
                return "git@bitbucket.org:{$this->name}.git";

            default:

                // Check of type 'scheme://host/path'
                if (filter_var($this->type, FILTER_VALIDATE_URL)) {
                    return $this->type;
                }

                // Check of type 'user@host'
                if (filter_var($this->type, FILTER_VALIDATE_EMAIL)) {
                    return "{$this->type}:{$this->name}.git";
                }

                return;
                break;
        }
    }

    /**
     * Get branch name.
     *
     * @return string
     */
    public function getBranch(): string
    {
        return is_null($this->version) ? 'master' : $this->version;
    }

    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getPluginName(): string
    {
        $parts = explode('/', $this->name);

        return Str::studly(end($parts));
    }

    /**
     * Get composer package name.
     *
     * @return string
     */
    public function getPackageName(): string
    {
        if (is_null($this->version)) {
            return $this->name.':dev-master';
        }

        return $this->name.':'.$this->version;
    }

    /**
     * Install the plugin via git.
     *
     * @return Process
     */
    public function installViaGit(): Process
    {
        return Process::fromShellCommandline(sprintf(
            'cd %s && git clone %s %s && cd %s && git checkout %s',
            base_path(),
            $this->getRepoUrl(),
            $this->getDestinationPath(),
            $this->getDestinationPath(),
            $this->getBranch()
        ));
    }

    /**
     * Install the plugin via git subtree.
     *
     * @return Process
     */
    public function installViaSubtree(): Process
    {
        return Process::fromShellCommandline(sprintf(
            'cd %s && git remote add %s %s && git subtree add --prefix=%s --squash %s %s',
            base_path(),
            $this->getPluginName(),
            $this->getRepoUrl(),
            $this->getDestinationPath(),
            $this->getPluginName(),
            $this->getBranch()
        ));
    }

    /**
     * Install the plugin via composer.
     *
     * @return Process
     */
    public function installViaComposer(): Process
    {
        return Process::fromShellCommandline(sprintf(
            'cd %s && composer require %s',
            base_path(),
            $this->getPackageName()
        ));
    }
}
