<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Tests;

use Jgut\Slim\PHPDI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;

/**
 * @internal
 */
class AppTest extends TestCase
{
    public function testCreation(): void
    {
        $container = ContainerBuilder::build();

        $app = $container->get(App::class);

        static::assertInstanceOf(App::class, $app);

        static::assertSame($container->get(ResponseFactoryInterface::class), $app->getResponseFactory());
        static::assertSame($container, $app->getContainer());
        static::assertSame($container->get(CallableResolverInterface::class), $app->getCallableResolver());
        static::assertSame($container->get(RouteCollectorInterface::class), $app->getRouteCollector());
        static::assertSame(
            $container->get(InvocationStrategyInterface::class),
            $app->getRouteCollector()
                ->getDefaultInvocationStrategy(),
        );
        static::assertSame($container->get(RouteResolverInterface::class), $app->getRouteResolver());
        static::assertSame($container->get(MiddlewareDispatcherInterface::class), $app->getMiddlewareDispatcher());
    }
}
