<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Mockery\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Yxx\LaravelPlugin\Support\CompressPlugin;
use Yxx\LaravelPlugin\Traits\HasMarketTokens;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class UploadCommand extends Command
{
    use PluginCommandTrait, HasMarketTokens;

    protected $name = 'plugin:upload';

    protected $description = 'Upload the plugin to the server.';

    public function handle(): int
    {
        try {
            $this->ensure_api_token_is_available();

            $plugin = $this->argument('plugin');
            $this->info("Plugin {$plugin} starts to compress");
            $compressRes = (new CompressPlugin($this->getPlugin()))->handle();
            if (! $compressRes) {
                $this->error("Plugin {$plugin} compression Failed");

                return E_ERROR;
            }
            $this->info("Plugin {$plugin} compression completed");

            $compressPath = $this->getPlugin()->getCompressFilePath();

            $stream = fopen($compressPath, 'r+');

            $size = (int) round(filesize($compressPath) / 1024, 2);

            $progressBar = $this->output->createProgressBar($size);
            $progressBar->setFormat(' %current%KB/%max%KB [%bar%] %percent:3s%% (%remaining:-6s% remaining)');
            $progressBar->start();

            $progressCallback = function ($_, $__, $___, $uploaded) use ($progressBar) {
                $progressBar->setProgress((int) round($uploaded / 1024, 2));
            };
            try {
                app('plugins.client')->upload([
                    'body' => $stream,
                    'headers' => ['plugin-info' => json_encode($this->getPlugin()->json()->getAttributes(), true)],
                    'progress' => $progressCallback,
                ]);
            } catch (\Exception $exception) {
                $this->line('');
                $this->error('Plugin upload failed : '.$exception->getMessage());

                return E_ERROR;
            }

            $progressBar->finish();
            $this->laravel['files']->delete($compressPath);
            $this->line('');
            $this->info('Plugin upload completed');

            if (is_resource($stream)) {
                fclose($stream);
            }

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::REQUIRED, 'The name of plugin will be used.'],
        ];
    }
}
