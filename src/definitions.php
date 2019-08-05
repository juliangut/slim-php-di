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

use Jgut\Slim\PHPDI\Configuration;
use Psr\Container\ContainerInterface;

return [
    // Replaced by used configuration
    Configuration::class => null,

    // Replaced by container itself
    ContainerInterface::class => null,
];
