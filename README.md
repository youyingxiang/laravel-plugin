# Laravel Plugin 

### [中文文档](http://docs.you-tang.com/)

## About Laravel Plugin
Laravel Plugin is a plugin mechanism solution for developers who need to build their own ecosystem，With it you can build a wordpress-like ecosystem. It can help you as follows：

* Load plugins based on service registration.
* Through the command line, plugin developers can easily and quickly build plugins and upload plug-ins to the plugin market.
* Provides plugin composer package support. Reference composer separately in the created plugin.
* Execute the logic of plugin installation, uninstallation, enable, and disable in the way of event monitoring. Easy for developers to expand.
* Slot-style plugin market support, by modifying the configuration file, developers can seamlessly connect to their own plugin market.
* Comes with a basic plugin marketplace, where developers can upload plugins and review them.
* Supports multiple versions of plugins.


## Requirement

* php >= 7.4
* laravel >= 8.0

## v1.1.0 Development Plan
* [x] Support laravel 9
* [ ] Replace FileRepository with MysqlRepository
* [ ] Complete `Laravel-plugin-ui`



## installation

* Step 1
```shell
composer require yxx/laravel-plugin
```

* Step 2
```php
php artisan vendor:publish --provider="Yxx\LaravelPlugin\Providers\PluginServiceProvider"
```














