[![PHP version](https://img.shields.io/badge/PHP-%3E%3D5.5-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)
[![License](https://img.shields.io/github/license/juliangut/slim-php-di.svg?style=flat-square)](https://github.com/juliangut/slim-php-di/blob/master/LICENSE)

[![Build status](https://img.shields.io/travis/juliangut/slim-php-di.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-php-di)
[![Style](https://styleci.io/repos/40728455/shield)](https://styleci.io/repos/40728455)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-php-di.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-php-di)
[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)

# Slim3 PHP-DI container integration

PHP-DI dependency injection container integration for Slim3 Framework.

Prepares PHP-DI container to fit in Slim3 App by registering default services in the container.

In order to allow possible services out there expecting the container to be `Slim\Container` and thus implementing `ArrayAccess`, it has been added to the container as well. You are encouraged to use ArrayAccess syntax for assignment instead of PHP-DI `set` method if you plan to reuse your code with default container.

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
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\App;

$settings = require __DIR__ . '/settings.php';
$definitions = [
    'my.parameter' => 'value',
    'Foo' => function (ContainerInterface $container) {
        return new \Foo($container->get('my.parameter'));
    },
    'Bar' => [\DI\get('BarFactory'), 'create'],
    'Baz' => \DI\object('Baz'),
];
$container = ContainerBuilder::build(new Configuration($settings), $definitions);

// Register services the PHP-DI way
$container->set('service_two', function (ContainerInterface $container) {
    return new ServiceTwo($container->get('service_two'));
});

// \Jgut\Slim\PHPDI\Container accepts registering services à la Pimple
$container['service_one'] =  function (ContainerInterface $container) {
    return new ServiceOne;
};

$app = new App($container);

// Set your routes

$app->run();
```

### App integration

Instead of `\Slim\App` you can use `\Jgut\Slim\PHPDI\App` which will build PHP-DI container.

```php
use Jgut\Slim\PHPDI\App;
use Jgut\Slim\PHPDI\Configuration;

$settings = require __DIR__ . '/settings.php';
$definitions = require __DIR__ . '/definitions.php';
$app = new App(new Configuration($settings), $definitions);

// Set your routes

$app->run();
```

### Configuration

Configurations for PHP-DI container builder.

```php
use Doctrine\Common\Cache\ArrayCache;
use Jgut\Slim\PHPDI\Configuration;

$settings = [
    'useAnnotations' => true,
    'ignorePhpDocErrors' => true,
];
$configuration = new Configuration($settings);

// Can be set after creation
$configuration->setDefinitionsCache(new ArrayCache());
$configuration->setProxiesPath(sys_get_temp_dir());
```

#### Available PHP-DI settings

* `useAutowiring`, whether or not to use autowiring (true by default)
* `useAnnotations`, whether or not to use annotations (false by default)
* `ignorePhpDocErrors`, whether or not to ignore phpDoc errors on annotations (false by default)
* `definitionsCache`, \Doctrine\Common\Cache\Cache (none by default)
* `proxiesPath`, path where PHP-DI creates its proxy files (none by default)
* `containerClass`, container class that will be built, must implement `\Interop\Container\ContainerInterface`, `\Di\FactoryInterface` and `\DI\InvokerInterface` (`\Jgut\Slim\PHPDI\Container` by default)

Refer to [PHP-DI documentation](http://php-di.org/doc/) to learn more about container configurations.

In order for you to use annotations you have to `require doctrine/annotations`. [See here](http://php-di.org/doc/annotations.html)

In order for you to use definitions cache you have to `require doctrine/cache`. [See here](http://php-di.org/doc/performances.html)

## Important note

Be aware that if you use cache then all your service definitions must be provided at container creation, and more importantly **do not set any definitions later on** as it is [not allowed](http://php-di.org/doc/php-definitions.html#setting-in-the-container-directly) at runtime when using cache (setting values at runtime is allowed though).

## Migration from 1.x

* PHP-DI settings have been moved out from definitions array into its own Configuration object. This object accepts an array of settings on instantiation so it's just a matter of providing settings to it.
* Configuration settings names have changed from snake_case to camelCase.
* There is only one place to override default Slim services, second argument of `build` method.

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-php-di/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-php-di/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-php-di/blob/master/LICENSE) included with the source code for a copy of the license terms.
