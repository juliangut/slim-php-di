<?php

/*
 * (c) 2015-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI;

use DI\CompiledContainer as DICompiledContainer;

/**
 * @see \Slim\Container
 */
abstract class AbstractCompiledContainer extends DICompiledContainer
{
    /** @phpstan-use ContainerTrait<object> */
    use ContainerTrait;
}
