# Laravel Plugin

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

## 命令行

### 查看所有可用指令

```php
php artisan plugin
```

### 创建插件
默认会在 plugins 目录下创建一个名为 demo 的插件，通过 `你的域名 + /demo` 现在就可以访问你创建插件的路由了。

```php
php artisan plugin:make demo
```

### 创建 provider

创建一个 TestServiceProvider 到 demo 插件

```php
php artisan plugin:make-provider  TestServiceProvider demo
```

### 创建控制器

创建一个 TestController 到 demo 插件
```php
php artisan plugin:make-controller  Test demo
```

### 创建 model

创建一个 TestController 到 demo 插件

```php
php artisan plugin:make-model Test demo
```

### 创建 migration

创建数据库表 tests 到 demo
```php
php artisan plugin:make-migration create_tests_table demo
```

### 执行数据迁移

单独执行 demo 插件的数据迁移
```php
php artisan plugin:migrate demo
```

所有插件进行数据迁移

```php
php artisan plugin:migrate 
```

### 插件安装 composer
在 demo 插件生产环境安装 composer 包

```php
php artisan plugin:composer-require demo spatie/data-transfer-object  
```

在 demo 插件 dev 环境安装 composer 包

```
php artisan plugin:composer-require demo spatie/data-transfer-object --dev
```

在 demo 插件安装指定版本的 composer 包

```
php artisan plugin:composer-require demo spatie/data-transfer-object --dev --v=3.6.0
```

### 插件卸载 composer
在 demo 插件卸载 composer 包 `spatie/data-transfer-object`
```php
php artisan plugin:composer-remove demo spatie/data-transfer-object
```


### 查看已经安装的插件
```php
php artisan plugin:list
```

### 启用插件
```php
php artisan plugin:enable demo
```

### 停用插件

```php
php artisan plugin:disable demo
```

### 安装插件
`/Users/Desktop/c516f6b8-e829-4743-bdb9-0098a1c29fec.zip` 就是你当前插件的本地路径
```php
php artisan plugin:install /Users/Desktop/c516f6b8-e829-4743-bdb9-0098a1c29fec.zip
```

### 删除插件
会移除当前插件相关的 composer 包,如果主程序或者其他插件也用到了这个 composer 包则不会移除。
```php
php artisan plugin:delete demo
```

### 静态资源
静态资源发布后将资源文件发布到 `public/plugins/demo` 目录下面
```php
php artisan plugin:publish demo
```

### 注册
开发者可以注册一个账号，以便可以将插件上传到插件市场，也可以在插件市场下载插件

```php
php artisan plugin:register
```

### 开发者登录

```
php artisan plugin:login
```

### 上传插件

将 demo 插件上传到插件市场
```
php artisan plugin:upload demo
```

### 远程下载插件并安装
请按提示安装对应的插件以及版本
```
php artisan plugin:download 
```

## 配置
在 `config/plugins` 可以配置我们的插件市场，默认的配置如下
```php
    // 插件市场
    'market' => [
        // 插件市场 api 域名
        'api_base' => 'http://plugin.you-tang.com/',
        // 插件市场默认调用的 client class
        'default' => \Yxx\LaravelPlugin\Support\Client\Market::class
    ],
```

`\Yxx\LaravelPlugin\Support\Client\Market::class` 默认调用的已经写好的插件市场 Api,如果你想调用自己的 Api，只需要实现 `Yxx\LaravelPlugin\Contracts\ClientInterface` 接口即可。


## 商业授权
如果您也喜欢 `laravel-plugin` 项目，期待开发者投入更多时间开发 `laravel-plugin`,那么就购买商业授权吧!

## 价格
如果你想将 laravel-plugin 进行商用，需要购买商业授权，目前授权的价格是 499 人民币。
购买商业授权以后，我这边会赠送一个 `laravel-plugin-market` 的市场插件包。你可以基于 `laravel-plugin-market` 或者参考它开发您的市场插件。

## laravel-plugin-market

### 介绍
`laravel-plugin-market` 是基于 `vue3 + tailwindcss` 构建的一个服务于 `laravel-plugin` 的插件市场。它是不开源的，如果你想拥有它，您只需要花 499 元购买 `laravel-plugin` 的商业授权以后，我们会给你提供 `laravel-plugin-market` 的代码。

### 功能点

* 常见插件市场的基础 `Api`
* UI 界面

可以对上传插件进行审核管理的后台

![file](https://cdn.learnku.com/uploads/images/202201/15/43464/wXWAUezcX3.png!large)

开发者登录以后可以看到自己上传的插件并提交审核

![file](https://cdn.learnku.com/uploads/images/202201/15/43464/lXLh0tFAGT.png!large)


## 交流讨论
微信群

![file](https://cdn.learnku.com/uploads/images/202201/15/43464/GGUaAxpnrN.png!large)

## 联系我

![file](https://cdn.learnku.com/uploads/images/202201/15/43464/GGUaAxpnrN.png!large)












