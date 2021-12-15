<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class PluginCommand extends Command
{
    protected $signature = 'plugin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all plugin commands.';

    /**
     * @var string
     */
    public static $logo = <<<LOGO
    __    ___    ____  ___ _    __________       ____  __    __  _____________   __
   / /   /   |  / __ \/   | |  / / ____/ /      / __ \/ /   / / / / ____/  _/ | / /
  / /   / /| | / /_/ / /| | | / / __/ / /      / /_/ / /   / / / / / __ / //  |/ / 
 / /___/ ___ |/ _, _/ ___ | |/ / /___/ /___   / ____/ /___/ /_/ / /_/ // // /|  /  
/_____/_/  |_/_/ |_/_/  |_|___/_____/_____/  /_/   /_____/\____/\____/___/_/ |_/   
                                                                                                                                                               
LOGO;

    public function handle(): void
    {
        $this->info(static::$logo);

        $this->comment('');
        $this->comment('Available commands:');
        $this->listAdminCommands();
    }

    protected function listAdminCommands(): void
    {
        $commands = collect(Artisan::all())->mapWithKeys(function ($command, $key) {
            if (Str::startsWith($key, 'plugin:')) {
                return [$key => $command];
            }

            return [];
        })->toArray();

        $width = $this->getColumnWidth($commands);

        /** @var Command $command */
        foreach ($commands as $command) {
            $this->info(sprintf(" %-{$width}s %s", $command->getName(), $command->getDescription()));
        }
    }

    private function getColumnWidth(array $commands): int
    {
        $widths = [];

        foreach ($commands as $command) {
            $widths[] = static::strlen($command->getName());
            foreach ($command->getAliases() as $alias) {
                $widths[] = static::strlen($alias);
            }
        }

        return $widths ? max($widths) + 2 : 0;
    }

    /**
     * Returns the length of a string, using mb_strwidth if it is available.
     *
     * @param  string  $string  The string to check its length
     * @return int The length of the string
     */
    public static function strlen($string): int
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }
}
