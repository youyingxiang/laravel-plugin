<?php

namespace Yxx\LaravelPlugin\Support\Composer;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\Macroable;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Json;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

abstract class Composer
{
    use Macroable;
    /**
     * @var RepositoryInterface|Application|mixed
     */
    protected RepositoryInterface $repository;

    protected ValRequires $requires;

    protected ValRequires $devRequires;

    protected ValRequires $removeRequires;

    public function __construct()
    {
        $this->requires = ValRequires::make();
        $this->devRequires = ValRequires::make();
        $this->removeRequires = ValRequires::make();
        $this->repository = app('plugins.repository');
    }

    /**
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * @return ValRequires
     */
    public function getRequires(): ValRequires
    {
        return $this->requires;
    }

    /**
     * @return ValRequires
     */
    public function getDevRequires(): ValRequires
    {
        return $this->devRequires;
    }

    /**
     * @return ValRequires
     */
    public function getRemoveRequires(): ValRequires
    {
        return $this->removeRequires;
    }

    /**
     * @param  ValRequires  $requires
     * @return $this
     *
     * @throws Exception
     */
    public function setRequires(ValRequires $requires): self
    {
        $this->requires = $this->filterExistRequires($requires);

        return $this;
    }

    /**
     * @param  ValRequires  $devRequires
     * @return $this
     */
    public function setDevRequires(ValRequires $devRequires): self
    {
        $this->devRequires = $this->filterExistRequires($devRequires);

        return $this;
    }

    /**
     * @param  ValRequires  $removeRequires
     * @return $this
     */
    public function setRemoveRequires(ValRequires $removeRequires): self
    {
        $this->removeRequires = $removeRequires->unique();

        return $this;
    }

    /**
     * @param  ValRequires  $removeValRequires
     * @return $this
     */
    public function appendRemoveRequires(ValRequires $removeValRequires): self
    {
        $this->removeRequires->merge($removeValRequires)->unique();

        return $this;
    }

    /**
     * @param  ValRequires  $devValRequires
     * @return $this
     *
     * @throws Exception
     */
    public function appendDevRequires(ValRequires $devValRequires): self
    {
        $this->devRequires = $this->filterExistRequires($this->devRequires->merge($devValRequires));

        return $this;
    }

    /**
     * @param  ValRequires  $valRequires
     * @return $this
     *
     * @throws Exception
     */
    public function appendRequires(ValRequires $valRequires): self
    {
        $this->requires = $this->filterExistRequires($this->requires->merge($valRequires));

        return $this;
    }

    /**
     * @return ValRequires
     *
     * @throws Exception
     */
    public function getExistRequires(): ValRequires
    {
        return ValRequires::toValRequires(Json::make(base_path('composer.json'))->setIsCache(false)->get('require'))
                ->merge(
                    ValRequires::toValRequires(Json::make(base_path('composer.json'))->setIsCache(false)->get('require-dev'))
                );
    }

    /**
     * @param  ValRequires  $requires
     * @return ValRequires
     *
     * @throws Exception
     */
    public function filterExistRequires(ValRequires $requires): ValRequires
    {
        return $requires->notIn($this->getExistRequires())->unique();
    }

    /**
     * @param  ValRequires  $requires
     * @param  bool  $isDev
     * @return string|null
     */
    public function getInstallRequiresCommand(ValRequires $requires, bool $isDev = false): ?string
    {
        $concatenatedPackages = '';
        if ($requires->empty()) {
            return null;
        }

        foreach ($requires->toArray() as $require) {
            $concatenatedPackages .= empty($require->version) ? "\"{$require->name}\" " : "\"{$require->name}:{$require->version}\" ";
        }

        return $isDev ? "composer require --dev {$concatenatedPackages}" : "composer require {$concatenatedPackages}";
    }

    /**
     * @param  ValRequires  $requires
     * @return string|null
     */
    public function getRemoveRequiresCommand(ValRequires $requires): ?string
    {
        if ($requires->empty()) {
            return null;
        }

        $concatenatedPackages = '';

        foreach ($requires->toArray() as $require) {
            $concatenatedPackages .= "\"{$require->name}\" ";
        }

        return "composer remove  {$concatenatedPackages}";
    }

    public function localeIsZhCN(): bool
    {
        return app()->getLocale() === 'zh-CN';
    }

    abstract public function beforeRun(): void;

    abstract public function afterRun(): void;

    public function run()
    {
        $this->beforeRun();

        chdir(base_path());

        if ($this->localeIsZhCN() && ! cache()->get('replace-mirror')) {
            passthru('composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/');
            cache()->set('replace-mirror', true);
        }

        if ($this->getRequires()->notEmpty()) {
            passthru($this->getInstallRequiresCommand($this->getRequires()));
        }

        if ($this->getDevRequires()->notEmpty()) {
            passthru($this->getInstallRequiresCommand($this->getDevRequires(), true));
        }

        if ($this->getRemoveRequires()->notEmpty()) {
            passthru($this->getRemoveRequiresCommand($this->getRemoveRequires()));
        }

        $this->afterRun();
    }
}
