[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)
[![License](https://img.shields.io/packagist/l/juliangut/slim-php-di.svg?style=flat-square)](https://github.com/juliangut/slim-php-di/blob/master/LICENSE)

[![Build status](https://img.shields.io/travis/juliangut/slim-php-di.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-php-di)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)

# Slim Framework PHP-DI container implementation

PHP-DI dependency injection container implementation for Slim Framework.

Prepares PHP-DI container to fit in Slim App by registering default Slim services in the container.

In order to allow possible services out there expecting the container to be `Slim\Container` and thus implement `ArrayAccess`, it has been introduced in this container as well. You are encouraged to use ArrayAccess syntax for assignment instead of PHP-DI `set` method.

## Installation

Best way to install is using [Composer](https://getcomposer.org/):

```
php composer.phar require juliangut/slim-php-di
```

Then require_once the autoload file:

```php
require_once './vendor/autoload.php';
```

## Usage

```php
use Jgut\Slim\PHPDI\ContainerBuilder;

$config = require_once __DIR__ . 'settings.php';
$container = ContainerBuilder::build($settings);

// Register services in the container
$container['my_service'] => function ($container) {
    return new \MyService;
);

$app = new \Slim\App($container);

// Set your routes

$app->run();
```

## Configuration

```php
$config = [
    'php-di' => [
        'use_annotations' => true,
        'definitions' => [
            'my.parameter' => 'value',
            'Foo' => [DI\get('FooFactory'), 'create'],
            'Bar' => function (ContainerInterface $container) {
                return new Bar($container->get('my.parameter'));
            },
            'Baz' => DI\object('Baz'),
            ...
        ],
    ],
];
```

Or you can separate PHP-DI definitions out into a single file and load it on container build.

```php
use Jgut\Slim\PHPDI\ContainerBuilder;

$config = require_once __DIR__ . 'settings.php';
$definitions = require_once __DIR__ . 'definitions.php';
$app = new \Slim\App(ContainerBuilder::build($settings, $definitions));

// Set your routes

$app->run();
```

### Available configurations

* `use_autowiring` boolean, whether to use or not autowiring (active by default)
* `use_annotations` boolean, whether to use or not annotations (not active by default)
* `ignore_phpdoc_errors` boolean, whether to ignore errors on phpDoc annotations
* `definitions_cache` \Doctrine\Common\Cache\CacheProvider
* `proxy_path` path where PHP-DI creates its proxy files
* `definitions` injection definitions for PHP-DI container

Please refere to [PHP-DI documentation](http://php-di.org/doc/) to learn more about container configurations,
specially on how to use [definitions](http://php-di.org/doc/definition.html) which is the key element on using this DI container.

*If you want to use annotations you have to require `doctrine/annotations` first*. More on this [here](http://php-di.org/doc/annotations.html)

*If you want to use definitions cache you have to require `doctrine/cache` first*. More on this [here](http://php-di.org/doc/performances.html)

#### Important note

Be aware that if you use cache you must provide `definitions` for all your services at container creation, and more importantly **not set any service later** as it is not allowed at runtime when using cache.

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-php-di/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-php-di/blob/master/CONTRIBUTING.md)

## License

### Release under BSD-3-Clause License.

See file [LICENSE](https://github.com/juliangut/slim-php-di/blob/master/LICENSE) included with the source code for a copy of the license terms.
