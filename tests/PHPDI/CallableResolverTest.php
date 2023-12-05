<?php

/*
 * (c) 2015-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Tests;

use InvalidArgumentException;
use Invoker\CallableResolver as InvokerResolver;
use Invoker\Exception\NotCallableException;
use Jgut\Slim\PHPDI\CallableResolver;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 */
class CallableResolverTest extends TestCase
{
    /**
     * @dataProvider provideResolveFromStringCases
     *
     * @param string|array<mixed>|object $toResolve
     * @param string|array<mixed>        $expectedResolvable
     */
    public function testResolveFromString(
        string $resolveMethod,
        string|array|object $toResolve,
        string|array $expectedResolvable,
    ): void {
        $invoker = $this->getMockBuilder(InvokerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('resolve')
            ->with($expectedResolvable)
            ->willReturn(static fn() => 'ok');

        $resolver = new CallableResolver($invoker);

        \call_user_func([$resolver, $resolveMethod], $toResolve);
    }

    /**
     * @return iterable<int, array{string, string|array<mixed>|object, string|array<mixed>}>
     */
    public static function provideResolveFromStringCases(): iterable
    {
        $handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $middleware = new class () implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        yield ['resolve', 'Service', 'Service'];
        yield ['resolve', 'Service:method', ['Service', 'method']];
        yield ['resolve', 'Service::method', ['Service', 'method']];
        yield ['resolve', ['Service', 'method'], ['Service', 'method']];

        yield ['resolveRoute', 'Controller', ['Controller', 'handle']];
        yield ['resolveRoute', $handler, [$handler, 'handle']];
        yield ['resolveRoute', [$handler, 'method'], [$handler, 'method']];

        yield ['resolveMiddleware', 'Middleware', ['Middleware', 'process']];
        yield ['resolveMiddleware', $middleware, [$middleware, 'process']];
        yield ['resolveMiddleware', [$middleware, 'method'], [$middleware, 'method']];
    }

    /**
     * @dataProvider provideNotResolvableCases
     *
     * @param string|array<mixed>|object $toResolve
     * @param string|array<mixed>        $expectedResolvable
     */
    public function testNotResolvable(
        string $resolveMethod,
        string|array|object $toResolve,
        string|array $expectedResolvable,
        string $expectedException,
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

        \call_user_func([$resolver, $resolveMethod], $toResolve);
    }

    /**
     * @return iterable<int, array{string, string|array<mixed>|object, string|array<mixed>, string}>
     */
    public static function provideNotResolvableCases(): iterable
    {
        $handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $middleware = new class () implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        yield ['resolve', 'Service', 'Service', 'Service'];
        yield ['resolve', 'Service:method', ['Service', 'method'], 'Service:method'];
        yield ['resolve', 'Service::method', ['Service', 'method'], 'Service::method'];
        yield [
            'resolve',
            ['Service', 'method'],
            ['Service', 'method'],
            json_encode(['Service', 'method'], \JSON_THROW_ON_ERROR),
        ];

        yield ['resolveRoute', 'Controller', ['Controller', 'handle'], 'Controller'];
        yield ['resolveRoute', $handler, [$handler, 'handle'], $handler::class];
        yield [
            'resolveRoute',
            [$handler, 'method'],
            [$handler, 'method'],
            json_encode([$handler, 'method'], \JSON_THROW_ON_ERROR),
        ];

        yield ['resolveMiddleware', 'Middleware', ['Middleware', 'process'], 'Middleware'];
        yield ['resolveMiddleware', $middleware, [$middleware, 'process'], $middleware::class];
        yield [
            'resolveMiddleware',
            [$middleware, 'method'],
            [$middleware, 'method'],
            json_encode([$middleware, 'method'], \JSON_THROW_ON_ERROR),
        ];
    }
}
