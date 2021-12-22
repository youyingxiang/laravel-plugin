<?php
namespace Yxx\LaravelPlugin\Support\Composer;

class ComposerInstall extends Composer
{
    public function handle(): void
    {
        $this->setRequires($this->repository->getComposerRequires("require"));
        $this->setDevRequires(
            array_filter(
                $this->repository->getComposerRequires("require-dev"),
                fn($devRequire) => ! in_array($devRequire, $this->requires)
            )
        );
        $this->run();
    }
}