[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)
[![License](https://img.shields.io/github/license/juliangut/slim-php-di.svg?style=flat-square)](https://github.com/juliangut/slim-php-di/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/slim-php-di.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-php-di)
[![Style Check](https://styleci.io/repos/40728455/shield)](https://styleci.io/repos/40728455)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-php-di.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-php-di)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-php-di.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-php-di)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di/stats)

# Slim Framework PHP-DI container integration

PHP-DI (v6) dependency injection container integration for Slim Framework.

In order to allow possible services out there expecting the container to be `Slim\Container` (extending Pimple) and thus implementing `ArrayAccess`, it has been added to default provided container.

You are encouraged to use array syntax for assignment instead of PHP-DI `set` method if you plan to reuse your code with default Slim container.

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

Use `\Jgut\Slim\PHPDI\App` which will build PHP-DI container.

```php
use Interop\Container\ContainerInterface;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\App;

$settings = require __DIR__ . '/settings.php';
$configuration = new Configuration($settings);
$configuration->setDefinitions('/path/to/definitions/file.php');

$app = new App($configuration);
$container = $app->getContainer();

// Register services the PHP-DI way
$container->set('service_one', function (ContainerInterface $container) {
    return new ServiceOne($container->get('service_two'));
});

// \Jgut\Slim\PHPDI\Container accepts registering services à la Pimple
$container['service_two'] =  function (ContainerInterface $container) {
    return new ServiceTwo();
};

// Set your routes

$app->run();
```

#### ContainerBuilder

Or build container and provide it to default Slim App.

```php
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContinerBuilder;
use Slim\App

$settings = require __DIR__ . '/settings.php';
$container = ContainerBuilder::build(new Configuration($settings));

// ...

$app = new App($container);

// ...

$app->run();
```

### Configuration

```php
use Jgut\Slim\PHPDI\Configuration;

$settings = [
    'useAnnotations' => true,
    'ignorePhpDocErrors' => true,
];
$configuration = new Configuration($settings);

// Can be set after creation
$configuration->setProxiesPath(sys_get_temp_dir());
$configuration->setDefinitions('/path/to/definitions/file.php');
```

#### PHP-DI settings

* `useAutoWiring`, whether or not to use auto wiring (true by default)
* `useAnnotations`, whether or not to use annotations (false by default)
* `ignorePhpDocErrors`, whether or not to ignore phpDoc errors on annotations (false by default)
* `wrapperContainer`, wrapping container (none by default)
* `proxiesPath`, path where PHP-DI creates its proxy files (none by default)
* `compilationPath`, path to where PHP-DI creates its compiled container (none by default)

Refer to [PHP-DI documentation](http://php-di.org/doc/) to learn more about container configurations.

In order for you to use annotations you have to `require doctrine/annotations`. [See here](http://php-di.org/doc/annotations.html)

#### Additional settings

* `containerClass`, container class that will be built. Must implement `\Interop\Container\ContainerInterface`, `\DI\FactoryInterface` and `\DI\InvokerInterface` (`\Jgut\Slim\PHPDI\Container` by default)
* `definitions`, an array of paths to definition files/directories or arrays of definitions. _Definitions are loaded in order of appearance_

## Services registration order

Services are registered in the following order:

* Default Slim services
* Definitions provided in configuration in the order they are in the array

## Migration from 1.x

* PHP-DI have been upgraded to v6. Review PHP-DI documentation: container compilation, create/autowire functions, etc
* PHP-DI settings have been moved into Configuration object. This object accepts an array of settings on instantiation so it's just a matter of providing the settings to it
* Configuration settings names have changed from snake_case to camelCase
* Definitions are included in Configuration object rather than set apart. Now you can as well define path(s) to load definition files from

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-php-di/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-php-di/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-php-di/blob/master/LICENSE) included with the source code for a copy of the license terms.
