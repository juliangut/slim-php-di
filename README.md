[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di)
[![License](https://img.shields.io/github/license/juliangut/slim-php-di.svg?style=flat-square)](https://github.com/juliangut/slim-php-di/blob/master/LICENSE)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-php-di.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-php-di/stats)

# Slim Framework PHP-DI container integration

PHP-DI dependency injection container integration for Slim framework.

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

Use `Jgut\Slim\PHPDI\ContainerBuilder` to create PHP-DI container and extract Slim's App from it

```php
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;

$container = ContainerBuilder::build(new Configuration());

$app = $container->get(App::class);
// same as $app = \Slim\Factory\AppFactory::createFromContainer($container);

// Register your services if not provided as definitions
$container->set('service_one', function (ContainerInterface $container): ServiceOne {
    return new ServiceOne($container->get('service_two'));
});

// Set your routes

$app->run();
```

_In order to register services in the container it's way better to do it in definition files_

### Configuration

```php
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;

$settings = [
    'ignorePhpDocErrors' => true,
    'compilationPath' => '/path/to/compiled/container',
];
$configuration = new Configuration($settings);

// Settings can be set after creation
$configuration->setProxiesPath(sys_get_temp_dir());
$configuration->setDefinitions('/path/to/definition/files');

$container = ContainerBuilder::build($configuration);
```

#### PHP-DI settings

* `useAutoWiring` whether to use auto wiring (true by default)
* `useAnnotations` whether to use annotations (false by default)
* `useDefinitionCache`, whether to use definition cache (false by default)
* `ignorePhpDocErrors`, whether to ignore phpDoc errors on annotations (false by default)
* `wrapContainer` wrapping container (none by default)
* `proxiesPath` path where PHP-DI creates its proxy files (none by default)
* `compilationPath` path where PHP-DI creates its compiled container (none by default)

Refer to [PHP-DI documentation](http://php-di.org/doc/) to learn more about container configurations

In order for you to use annotations you have to `require doctrine/annotations`. [Review documentation](http://php-di.org/doc/annotations.html)

#### Additional settings

* `definitions` an array of paths to definition files/directories or arrays of definitions. _Definitions are loaded in order of appearance_
* `containerClass` container class used on the build. Must implement `\Psr\Container\ContainerInterface`, `\DI\FactoryInterface` and `\DI\InvokerInterface` (`\Jgut\Slim\PHPDI\Container` by default)

## Container array access shorthand

Default `\Jgut\Slim\PHPDI\Container` container allows shorthand array access by concatenating array keys with dots. If any key in the chain is not defined, normal `Psr\Container\NotFoundExceptionInterface` exception is thrown

```php
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;

$container = ContainerBuilder::build(new Configuration([]));

$container->get('configs')['database']['dsn']; // given "configs" is an array
$container->get('configs.database.dsn'); // same as above
```

#### Notice

Be careful though not to shadow any array key by using dots in keys itself

```php
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;

$container = ContainerBuilder::build(new Configuration([]));

$configs = [
    'foo' => [
        'bar' => [
            'baz' => 'shadowed!', // <== watch out!
        ],
    ],
    'foo.bar' => 'bingo!',
];
$container->set('configs', $configs);

$container->get('configs.foo.bar'); // bingo!
$container->get('configs.foo.bar.baz'); // NotFoundExceptionInterface thrown
```

_The easiest way to avoid this from ever happening is by NOT using dots in array keys_

## Invocation strategy

By default, slim-php-di sets a custom invocation strategy that employs PHP-DI's Invoker to fulfill callable parameters, it is quite handy and lets you do things like this

```php
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

$container = ContainerBuilder::build(new Configuration([]));

$app = $container->get(App::class);

$app->get('/hello/{name}', function (ResponseInterface $response, string $name, \PDO $connection): ResponseInterface {
    // $name will be injected from request arguments
    // $connection will be injected from the container

    $response->getBody()->write('Hello ' . $name);

    return $response;
});

$app->run();
```

If you prefer default Slim's `Slim\Handlers\Strategies\RequestResponse` strategy or any other of your choosing you only have to set it in a definition file

```php
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Interfaces\InvocationStrategyInterface;

use function DI\create;

return [
    InvocationStrategyInterface::class => create(RequestResponse::class),
];
```

## Migration from 3.x

* PHP minimum required version is PHP 8.0
* Moved to PHP-DI 7. Annotations have been removed, use attributes

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-php-di/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-php-di/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-php-di/blob/master/LICENSE) included with the source code for a copy of the license terms.
