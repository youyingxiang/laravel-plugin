<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Support\Config;

class RegisterCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'plugin:register';

    /**
     * @var string
     */
    protected $description = 'register to the plugin server.';

    public function handle(): int
    {
        try {
            $name = $this->ask('UserName');
            $account = $this->ask('Email');
            $password = $this->secret('Password');
            if (Str::length($password) < 8) {
                throw new \InvalidArgumentException('The password must be at least 8 characters.');
            }
            $passwordConfirmation = $this->secret('Confirmation Password');

            if ($passwordConfirmation !== $password) {
                throw new \InvalidArgumentException('The password confirmation does not match.');
            }

            $result = app('plugins.client')->register(
                $account,
                $name,
                $password,
                $passwordConfirmation
            );

            $this->store(data_get($result, 'token'));

            return 0;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function store(string $token): void
    {
        Config::set('token', $token);

        $this->info('Authenticated successfully.'.PHP_EOL);
    }
}
