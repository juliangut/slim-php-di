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

namespace Jgut\Slim\PHPDI\Tests;

use Jgut\Slim\PHPDI\App;
use Jgut\Slim\PHPDI\Container;
use PHPUnit\Framework\TestCase;

/**
 * App tests.
 */
class AppTest extends TestCase
{
    public function testDefaultCreation()
    {
        $app = new App();

        self::assertInstanceOf(Container::class, $app->getContainer());
    }
}
