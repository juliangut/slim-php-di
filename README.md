[![Latest Version](https://img.shields.io/github/release/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)
[![License](https://img.shields.io/packagist/l/juliangut/slim-php-di.svg?style=flat-square)](https://github.com/juliangut/slim-php-di/blob/master/LICENSE)

[![Build status](https://img.shields.io/travis/juliangut/slim-php-di.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-php-di)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)

# Slim Framework PHP-DI container implementation

PHP-DI dependency injection container implementation for Slim Framework.

Prepares PHP-DI container to fit in Slim App by registering default services in the container.

Implements ArrayAccess interface to mimic Slim's default container services access based on Pimple.

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

$app = new \Slim\App($container);

// Define your routes here

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
$container = ContainerBuilder::build($settings, $definitions);

$app = new \Slim\App($container);

// Define your routes here

$app->run();
```

### Available configurations

* `use_autowiring` boolean, wether to use or not autowiring (active by default)
* `use_annotations` boolean, wether to use or not annotations (not active by default)
* `ignore_phpdoc_errors` boolean, wether to ignore errors on phpDoc annotations
* `proxy_path` path where PHP-DI creates its proxy files
* `definitions` injection definitions for PHP-DI container

Please refere to [PHP-DI documentation](http://php-di.org/doc/) to learn more about container configurations,
specially on how to use [definitions](http://php-di.org/doc/definition.html) which is the key element on using this DI container.

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-php-di/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-php-di/blob/master/CONTRIBUTING.md)

## License

### Release under BSD-3-Clause License.

See file [LICENSE](https://github.com/juliangut/slim-php-di/blob/master/LICENSE) included with the source code for a copy of the license terms.
