<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Slim\PHPDI\Tests;

use Jgut\Slim\PHPDI\App;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\Container;

/**
 * App tests.
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $app = new App(new Configuration);

        self::assertInstanceOf(Container::class, $app->getContainer());
    }
}
