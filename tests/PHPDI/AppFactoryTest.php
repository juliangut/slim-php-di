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
use Slim\Handlers\Strategies\RequestResponse;

/**
 * AppFactory tests.
 */
class AppFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        AppFactory::setContainerConfiguration(new Configuration());

        $app = AppFactory::create();

        self::assertInstanceOf(App::class, $app);
    }

    public function testCreationWithInvocationStrategy(): void
    {
        /** @var \Slim\Interfaces\InvocationStrategyInterface $strategy */
        $strategy = $this->getMockBuilder(RequestResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        AppFactory::setInvocationStrategy($strategy);
        AppFactory::setContainerConfiguration(new Configuration());

        $app = AppFactory::create();

        self::assertInstanceOf(App::class, $app);
        self::assertSame($strategy, $app->getRouteCollector()->getDefaultInvocationStrategy());
    }
}
