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

use InvalidArgumentException;
use Invoker\CallableResolver as InvokerResolver;
use Invoker\Exception\NotCallableException;
use Jgut\Slim\PHPDI\CallableResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 */
class CallableResolverTest extends TestCase
{
    /**
     * @dataProvider resolveFromStringProvider
     *
     * @param string|array<mixed>|object $toResolve
     * @param string|array<mixed>        $expectedResolvable
     */
    public function testResolveFromString(string $resolveMethod, $toResolve, $expectedResolvable): void
    {
        $invoker = $this->getMockBuilder(InvokerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('resolve')
            ->with($expectedResolvable)
            ->willReturn(static fn() => 'ok');

        $resolver = new CallableResolver($invoker);

        $resolver->{$resolveMethod}($toResolve);
    }

    /**
     * @return array<mixed>
     */
    public function resolveFromStringProvider(): array
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
     * @dataProvider notResolvableProvider
     *
     * @param string|array<mixed>|object $toResolve
     * @param string|array<mixed>        $expectedResolvable
     */
    public function testNotResolvable(
        string $resolveMethod,
        $toResolve,
        $expectedResolvable,
        string $expectedException
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('"%s" is not resolvable.', $expectedException));

        $invoker = $this->getMockBuilder(InvokerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('resolve')
            ->with($expectedResolvable)
            ->willThrowException(new NotCallableException());

        $resolver = new CallableResolver($invoker);

        $resolver->{$resolveMethod}($toResolve);
    }

    /**
     * @return array<mixed>
     */
    public function notResolvableProvider(): array
    {
        $controller = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();

        return [
            ['resolve', 'Service', 'Service', 'Service'],
            ['resolve', 'Service:method', ['Service', 'method'], 'Service:method'],
            ['resolve', 'Service::method', ['Service', 'method'], 'Service::method'],
            ['resolve', ['Service', 'method'], ['Service', 'method'], json_encode(['Service', 'method'])],

            ['resolveRoute', 'Controller', ['Controller', 'handle'], 'Controller'],
            ['resolveRoute', $controller, [$controller, 'handle'], \get_class($controller)],
            ['resolveRoute', [$controller, 'method'], [$controller, 'method'], json_encode([$controller, 'method'])],

            ['resolveMiddleware', 'Middleware', ['Middleware', 'process'], 'Middleware'],
            ['resolveMiddleware', $middleware, [$middleware, 'process'], \get_class($middleware)],
            [
                'resolveMiddleware',
                [$middleware, 'method'],
                [$middleware, 'method'],
                json_encode([$middleware, 'method']),
            ],
        ];
    }
}
