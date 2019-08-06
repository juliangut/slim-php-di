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

use Jgut\Slim\PHPDI\AppFactory;
use Jgut\Slim\PHPDI\Configuration;
use PHPUnit\Framework\TestCase;
use Slim\App;

/**
 * AppFactory tests.
 */
class AppFactoryTest extends TestCase
{
    public function testCreation()
    {
        AppFactory::setUseCustomStrategy(true);
        AppFactory::setContainerConfiguration(new Configuration());

        $app = AppFactory::create();

        self::assertInstanceOf(App::class, $app);
    }
}
