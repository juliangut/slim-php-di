<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI;

use DI\CompiledContainer as DICompiledContainer;

/**
 * PHP-DI compiled Dependency Injection Container.
 * Implements ArrayAccess to accommodate to default Slim container based in Pimple.
 *
 * @see \Slim\Container
 */
abstract class AbstractCompiledContainer extends DICompiledContainer implements \ArrayAccess
{
    use ContainerTrait;
}
