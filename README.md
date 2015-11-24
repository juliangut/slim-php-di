[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)
[![License](https://img.shields.io/github/license/juliangut/slim-php-di.svg?style=flat-square)](https://github.com/juliangut/slim-php-di/blob/master/LICENSE)

[![Build status](https://img.shields.io/travis/juliangut/slim-php-di.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-php-di)
[![Style](https://styleci.io/repos/40728455/shield)](https://styleci.io/repos/40728455)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-php-di.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-php-di)
[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)

# Slim Framework PHP-DI container implementation

PHP-DI dependency injection container implementation for Slim Framework.

Prepares PHP-DI container to fit in Slim App by registering default Slim services in the container.

In order to allow possible services out there expecting the container to be `Slim\Container` and thus implement `ArrayAccess`, it has been introduced in this container as well. You are encouraged to use ArrayAccess syntax for assignment instead of PHP-DI `set` method.

## Installation

Best way to install is using [Composer](https://getcomposer.org/):

```
composer require juliangut/slim-php-di
```

Then require_once the autoload file:

```php
require_once './vendor/autoload.php';
```

## Usage

```php
use Jgut\Slim\PHPDI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\App;

$settings = require __DIR__ . 'settings.php';
$container = ContainerBuilder::build($settings);

// Register services the Pimple way
$container['service_one'] =  function (ContainerInterface $container) {
    return new ServiceOne;
};

// Register services the PHP-DI way
$container->set('service_two', function (ContainerInterface $container) {
    return new ServiceTwo($container->get('service_one'));
});

$app = new App($container);

// Set your routes

$app->run();
```

### Configuration

You can define your services definitions in settings array as it's done with default Slim Container

```php
use Jgut\Slim\PHPDI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\App;
use function DI\get;
use function DI\object;

$settings = [
    'settings' => [
        // Specific settings for PHP-DI container
        'php-di' => [
            'use_autowiring' => true,
            'use_annotations' => true,
        ],
    ],
    // Services definitions
    'my.parameter' => 'value',
    'Foo' => function (ContainerInterface $container) {
        return new Foo($container->get('my.parameter'));
    },
    'Bar' => [get('BarFactory'), 'create'],
    'Baz' => object('Baz'),
];
$container = ContainerBuilder::build($settings);
$app = new App($container);
```

Or you can separate service definitions and load them on container build.

```php
use Jgut\Slim\PHPDI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\App;
use function DI\get;
use function DI\object;

$settings = [
    'settings' => [
        // Specific settings for PHP-DI container
        'php-di' => [
            'use_autowiring' => true,
            'use_annotations' => true,
        ],
    ],
];
// Services definitions
$definitions = [
    'my.parameter' => 'value',
    'Foo' => function (ContainerInterface $container) {
        return new Foo($container->get('my.parameter'));
    },
    'Bar' => [get('BarFactory'), 'create'],
    'Baz' => object('Baz'),
];
$container = ContainerBuilder::build($settings, $definitions);
$app = new App($container);
```

#### Available PHP-DI settings

PHP-DI container is configured under `php-di` settings key as shown in previous examples.

* `use_autowiring` boolean, whether or not to use autowiring (true by default)
* `use_annotations` boolean, whether or not to use annotations (false by default)
* `ignore_phpdoc_errors` boolean, whether or not to ignore phpDoc errors on annotations (false by default)
* `proxy_path` path where PHP-DI creates its proxy files
* `definitions_cache` \Doctrine\Common\Cache\Cache

Refere to [PHP-DI documentation](http://php-di.org/doc/) to learn more about container configurations,
specially on how to use definitions which is the key element on using PHP-DI.

In order for you to use annotations you have to `require doctrine/annotations`. [See here](http://php-di.org/doc/annotations.html)

In order for you to use definitions cache you have to `require doctrine/cache`. [See here](http://php-di.org/doc/performances.html)

### Registration order

Services registration order is:

* Default Slim services
* Definitions on settings array
* Definitions on second argument of `build` method

In order to override default Slim services add them in settings array or defined on second argument of `build` method.

```php
use Jgut\Slim\PHPDI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\App;

$settings = [
    'errorHandler' => function (ContainerInterface $container) {
        return new MyErrorHandler($container->get('settings')['displayErrorDetails']);
    },
];
$container = ContainerBuilder::build($settings);
$app = new App($container);
```

## Important note

Be aware that if you use cache you must provide definitions for all your services at container creation, and more importantly **do not set any definitions later on** as it is [not allowed](http://php-di.org/doc/php-definitions.html#setting-in-the-container-directly) at runtime when using cache (setting values at runtime is allowed though).

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-php-di/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-php-di/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-php-di/blob/master/LICENSE) included with the source code for a copy of the license terms.
