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

use Invoker\CallableResolver as InvokerResolver;
use Invoker\Exception\NotCallableException;
use Jgut\Slim\PHPDI\CallableResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CallableResolver tests.
 */
class CallableResolverTest extends TestCase
{
    /**
     * @dataProvider getResolvableList
     *
     * @param string                $resolveMethod
     * @param string|mixed[]|object $toResolve
     * @param string|mixed[]        $expectedResolvable
     */
    public function testResolveFromString(
        string $resolveMethod,
        $toResolve,
        $expectedResolvable
    ): void {
        $invoker = $this->getMockBuilder(InvokerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('resolve')
            ->with($expectedResolvable)
            ->willReturn(function () {
                return 'ok';
            });

        $resolver = new CallableResolver($invoker);

        $resolver->{$resolveMethod}($toResolve);
    }

    /**
     * @return mixed[]
     */
    public function getResolvableList(): array
    {
        $controller = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();

        return [
            ['resolve', 'Service', 'Service'],
            ['resolve', 'Service:method', ['Service', 'method']],
            ['resolve', 'Service::method', ['Service', 'method']],
            ['resolve', ['Service', 'method'], ['Service', 'method']],

            ['resolveRoute', 'Controller', ['Controller', 'handle']],
            ['resolveRoute', $controller, [$controller, 'handle']],
            ['resolveRoute', [$controller, 'method'], [$controller, 'method']],

            ['resolveMiddleware', 'Middleware', ['Middleware', 'process']],
            ['resolveMiddleware', $middleware, [$middleware, 'process']],
            ['resolveMiddleware', [$middleware, 'method'], [$middleware, 'method']],
        ];
    }

    /**
     * @dataProvider getNotResolvableList
     *
     * @param string                $resolveMethod
     * @param string|mixed[]|object $toResolve
     * @param string|mixed[]        $expectedResolvable
     * @param string                $expectedExceptionType
     */
    public function testNotResolvable(
        string $resolveMethod,
        $toResolve,
        $expectedResolvable,
        string $expectedExceptionType
    ): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('"%s" is not resolvable', $expectedExceptionType));

        $invoker = $this->getMockBuilder(InvokerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('resolve')
            ->with($expectedResolvable)
            ->will(self::throwException(new NotCallableException()));

        $resolver = new CallableResolver($invoker);

        $resolver->{$resolveMethod}($toResolve);
    }

    public function getNotResolvableList(): array
    {
        $controller = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();

        return [
            ['resolve', 'Service', 'Service', 'Service'],
            ['resolve', 'Service:method', ['Service', 'method'], 'Service:method'],
            ['resolve', 'Service::method', ['Service', 'method'], 'Service::method'],
            ['resolve', ['Service', 'method'], ['Service', 'method'], \json_encode(['Service', 'method'])],

            ['resolveRoute', 'Controller', ['Controller', 'handle'], 'Controller'],
            ['resolveRoute', $controller, [$controller, 'handle'], \get_class($controller)],
            ['resolveRoute', [$controller, 'method'], [$controller, 'method'], \json_encode([$controller, 'method'])],

            ['resolveMiddleware', 'Middleware', ['Middleware', 'process'], 'Middleware'],
            ['resolveMiddleware', $middleware, [$middleware, 'process'], \get_class($middleware)],
            [
                'resolveMiddleware',
                [$middleware, 'method'],
                [$middleware, 'method'],
                \json_encode([$middleware, 'method']),
            ],
        ];
    }
}
