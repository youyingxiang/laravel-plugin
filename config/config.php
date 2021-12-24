<?php

return [

    /*
    |--------------------------------------------------------------------------
    | plugin Namespace
    |--------------------------------------------------------------------------
    |
    | Default plugin namespace.
    |
    */

    'namespace' => 'Plugins',

    /*
    |--------------------------------------------------------------------------
    | Plugin Stubs
    |--------------------------------------------------------------------------
    |
    | Default plugin stubs.
    |
    */

    'stubs' => [
        'enabled' => false,
        'files'   => [
            'routes/web'      => 'Routes/web.php',
            'routes/api'      => 'Routes/api.php',
            'views/index'     => 'Resources/views/index.blade.php',
            'views/master'    => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
            'assets/js/app'   => 'Resources/assets/js/app.js',
            'assets/sass/app' => 'Resources/assets/sass/app.scss',
        ],
        'replacements' => [
            'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api'      => ['LOWER_NAME'],
            'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'PLUGIN_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index'     => ['LOWER_NAME'],
            'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
        ],
        'gitkeep' => true,
    ],
    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Plugins path
        |--------------------------------------------------------------------------
        |
        */

        'plugins' => base_path('plugins'),
        /*
        |--------------------------------------------------------------------------
        | Plugins assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the plugin assets path.
        |
        */

        'assets' => public_path('plugins'),

        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Set the generate key to false to not generate that folder
        */
        'generator' => [
            'config'     => ['path' => 'Config', 'generate' => true],
            'seeder'     => ['path' => 'Database/Seeders', 'generate' => true],
            'routes'     => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'provider'   => ['path' => 'Providers', 'generate' => true],
            'assets'     => ['path' => 'Resources/assets', 'generate' => true],
            'lang'       => ['path' => 'Resources/lang', 'generate' => true],
            'views'      => ['path' => 'Resources/views', 'generate' => true],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Package commands
    |--------------------------------------------------------------------------
    |
    | Here you can define which commands will be visible and used in your
    | application. If for example you don't use some of the commands provided
    | you can simply comment them out.
    |
    */
    'commands' => [],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache' => [
        'enabled'  => false,
        'key'      => 'laravel-plugin',
        'lifetime' => 60,
    ],
    /*
    |--------------------------------------------------------------------------
    | Choose what laravel-plugins will register as custom namespaces.
    | Setting one to false will require you to register that part
    | in your own Service Provider class.
    |--------------------------------------------------------------------------
    */
    'register' => [
        'translations' => true,
        /**
         * load files on boot or register method.
         *
         * Note: boot not compatible with asgardcms
         *
         * @example boot|register
         */
        'files' => 'register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | You can define new types of activators here, file, database etc. The only
    | required parameter is 'class'.
    | The file activator will store the activation status in storage/installed_plugins
    */
    'activators' => [
        'file' => [
            'class'          => \Yxx\LaravelPlugin\Activators\FileActivator::class,
            'statuses-file'  => base_path('plugin_statuses.json'),
            'cache-key'      => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],

    'activator' => 'file',
];
