<?php
namespace Yxx\LaravelPlugin\Support\Composer;

use Illuminate\Contracts\Foundation\Application;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;

abstract class Composer
{
    /**
     * @var RepositoryInterface|Application|mixed
     */
    protected RepositoryInterface $repository;

    protected array $requires = [];

    protected array $devRequires = [];

    protected array $removeRequires = [];

    public function __construct()
    {
        $this->repository = app('plugins.repository');
    }

    /**
     * @return array
     */
    public function getRequires(): array
    {
        return $this->requires;
    }

    /**
     * @return array
     */
    public function getDevRequires(): array
    {
        return $this->devRequires;
    }

    /**
     * @return array
     */
    public function getRemoveRequires(): array
    {
        return $this->removeRequires;
    }

    /**
     * @param  array  $requires
     * @return $this
     */
    public function setRequires(array $requires):self
    {
        $this->requires = $requires;

        return $this;
    }

    /**
     * @param  array  $devRequires
     * @return $this
     */
    public function setDevRequires(array $devRequires): self
    {
        $this->devRequires = $devRequires;

        return $this;
    }

    /**
     * @param  array  $removeRequires
     * @return $this
     */
    public function setRemoveRequires(array $removeRequires): self
    {
        $this->removeRequires = $removeRequires;

        return $this;
    }

    /**
     * @param  array  $removeRequires
     * @return $this
     */
    public function appendRemoveRequires(array $removeRequires): self
    {
        $this->removeRequires = array_merge($this->removeRequires, $removeRequires);
        return $this;
    }

    /**
     * @param  array  $devRequires
     * @return $this
     */
    public function appendDevRequires(array $devRequires): self
    {
        $this->devRequires = array_merge($this->devRequires, $devRequires);
        return $this;
    }

    /**
     * @param  array  $requires
     * @return $this
     */
    public function appendRequires(array $requires): self
    {
        $this->requires = array_merge($this->requires, $requires);
        return $this;
    }


    /**
     * @param  array  $requires
     * @param  bool  $isDev
     * @return string|null
     */
    public function getInstallRequiresCommand(array $requires, bool $isDev = false): ?string
    {
        if (! $requires) {
            return null;
        }
        $concatenatedPackages = '';

        foreach ($requires as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        return $isDev ? "composer require --dev {$concatenatedPackages}" : "composer require {$concatenatedPackages}";
    }

    /**
     * @param  array  $requires
     * @return string|null
     */
    public function getRemoveRequiresCommand(array $requires): ?string
    {
        if (! $requires) {
            return null;
        }

        $concatenatedPackages = "";

        foreach ($requires as $name => $version) {
            $concatenatedPackages .= "\"{$name}\" ";
        }

        return "composer remove  {$concatenatedPackages}";
    }

    public function localeIsZhCN(): bool
    {
        return app()->getLocale() === "zh-CN";
    }

    public function run()
    {
        chdir(base_path());

        if ($this->localeIsZhCN() && ! cache()->get("replace-mirror")) {
            passthru("composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/");
            cache()->set("replace-mirror", true);
        }

        if ($this->getRequires()) {
            passthru($this->getInstallRequiresCommand($this->getRequires()));
        }

        if ($this->getDevRequires()) {
            passthru($this->getInstallRequiresCommand($this->getDevRequires(), true));
        }

        if ($this->getRemoveRequires()) {
            passthru($this->getRemoveRequiresCommand($this->getRemoveRequires()));
        }
    }
    
}