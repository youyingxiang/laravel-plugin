<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Yxx\LaravelPlugin\Support\Config;

class LoginCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'plugin:login';

    /**
     * @var string
     */
    protected $description = 'Login to the plugin server.';

    public function handle(): int
    {
        try {
            $result = app('plugins.client')->login(
                $email = $this->ask('Account'),
                $password = $this->secret('Password')
            );
            $this->store(data_get($result, 'token'));

            return 0;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function store($token): void
    {
        Config::set('token', $token);

        $this->info('Authenticated successfully.'.PHP_EOL);
    }
}
