<?php

namespace Yxx\LaravelPlugin\Support;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Yxx\LaravelPlugin\Exceptions\CompressPluginException;
use ZipArchive;

class CompressPlugin
{
    /**
     * @var Plugin
     */
    protected Plugin $plugin;

    /**
     * CompressPlugin constructor.
     *
     * @param  Plugin  $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return bool
     *
     * @throws CompressPluginException
     */
    public function handle(): bool
    {
        if (! $this->plugin->getFiles()->isDirectory($this->plugin->getCompressDirectoryPath())) {
            $this->plugin->getFiles()->makeDirectory($this->plugin->getCompressDirectoryPath(), 0775, true);
        }
        if (PHP_OS == 'Darwin') {
            $this->compressPluginOnMac();

            $this->ensureArchiveIsWithinSizeLimits();

            return true;
        }

        $compressFiles = Finder::create()
            ->in($this->plugin->getPath())
            ->files()
            ->ignoreVcs(true)
            ->ignoreDotFiles(false);

        $archive = new ZipArchive();

        $archive->open($this->plugin->getCompressFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($compressFiles as $compressFile) {
            $relativePathName = str_replace('\\', '/', $compressFile->getRelativePathname());

            $archive->addFile($compressFile->getRealPath(), $relativePathName);

            $archive->setExternalAttributesName(
                $relativePathName,
                ZipArchive::OPSYS_UNIX,
                ($this->getPermissions($compressFile) & 0xFFFF) << 16
            );
        }
        $archive->close();

        $this->ensureArchiveIsWithinSizeLimits();

        return true;
    }

    protected function compressPluginOnMac(): void
    {
        (new Process(['zip', '-r', $this->plugin->getCompressFilePath(), '.'], $this->plugin->getPath()))->mustRun();
    }

    protected function ensureArchiveIsWithinSizeLimits(): void
    {
        $size = ceil(filesize($this->plugin->getCompressFilePath()) / 1048576);

        if ($size > 250) {
            throw new CompressPluginException('Application is greater than 250MB. Your application is '.$size.'MB.');
        }
    }

    /**
     * Get the proper file permissions for the file.
     *
     * @param  SplFileInfo  $file
     * @return int
     */
    protected function getPermissions(SplFileInfo $file): int
    {
        return $file->isDir() || $file->getFilename() == 'php'
            ? 33133  // '-r-xr-xr-x'
            : fileperms($file->getRealPath());
    }
}
