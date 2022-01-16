<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Traits\HasMarketTokens;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class DownLoadCommand extends Command
{
    use PluginCommandTrait, HasMarketTokens;

    protected $name = 'plugin:download';

    protected $description = 'Download plugin from server to local.';

    public function handle(): int
    {
        $path = Str::uuid().'.zip';
        try {
            $this->ensure_api_token_is_available();
            $plugins = data_get(app('plugins.client')->plugins(1), 'data');
            $rows = array_reduce($plugins, function ($rows, $item) {
                $rows[] = [
                    count($rows),
                    $item['name'],
                    $item['author'],
                    $item['download_times'],
                ];

                return $rows;
            }, []);
            $this->comment(__('plugins.plugin_list'));

            $this->table([
                __('plugins.serial_number'),
                __('plugins.name'),
                __('plugins.author'),
                __('plugins.download_times'),
            ], $rows);

            $sn = $this->ask(__('plugins.input_sn'));

            if (! $plugin = data_get($plugins, $sn)) {
                throw new \InvalidArgumentException(__('plugins.sn_not_exist'));
            }

            $versions = array_map(fn ($version) => [
                $version['id'],
                $version['version'],
                $version['description'],
                $version['download_times'],
                $version['status_str'],
                $version['price'],
            ], data_get($plugin, 'versions'));

            $this->comment(__('plugins.version_list'));

            $this->table([
                __('plugins.id'),
                __('plugins.version'),
                __('plugins.description'),
                __('plugins.download_times'),
                __('plugins.status'),
                __('plugins.price'),
            ], $versions);

            $versionId = $this->ask(__('plugins.input_version_id'));

            if (! in_array($versionId, Arr::pluck($plugin['versions'], 'id'))) {
                throw new \InvalidArgumentException(__('plugins.version_not_exist'));
            }

            Storage::put($path, app('plugins.client')->download($versionId));

            Artisan::call('plugin:install', ['path' => Storage::path($path)]);

            $this->info(__('plugins.download_successful'));
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        } finally {
            Storage::delete($path);
        }

        return 0;
    }
}
