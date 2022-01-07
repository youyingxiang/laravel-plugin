<?php

namespace Yxx\LaravelPlugin\Support\Composer;

use Yxx\LaravelPlugin\Exceptions\ComposerException;

class ComposerInstall extends Composer
{
    public function beforeRun(): void
    {
        $this->setRequires($requires = $this->repository->getComposerRequires('require'));
        $this->setDevRequires($this->repository->getComposerRequires('require-dev')->notIn($requires));
    }

    public function afterRun(): void
    {
        $failedrequires = $this->filterExistRequires($this->getRequires()->merge($this->getDevRequires()));

        if ($failedrequires->notEmpty()) {
            throw new ComposerException("Package {$failedrequires} installation failed");
        }
    }
}
