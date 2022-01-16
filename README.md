<div align="center">
    <img src="https://cdn.learnku.com/uploads/images/202201/15/43464/4ceH3kMCms.png!large">
</div>

### [中文文档](http://docs.you-tang.com/)

## 介绍
Laravel Plugin 是为需要构建自己生态的开发者提供的插件机制解决方案，使用它您可以构建类似 wordpress 的生态。它能为您提供的帮助如下：

* 基于服务注册的方式去加载插件。
* 通过命令行的方式，插件开发者可以方便快捷的构建插件，上传插件到插件市场。
* 提供插件 composer 包支持。
* 插槽式的插件市场支持，通过修改配置文件，开发者可以无缝对接到自己的插件市场。


## 环境依赖

* php 7.4 以上版本
* laravel 6.0 以上版本


## 安装

* 第一步
```shell
composer require yxx/laravel-plugin
```

* 第二步
```php
php artisan vendor:publish --provider="Yxx\LaravelPlugin\Providers\PluginServiceProvider"
```














