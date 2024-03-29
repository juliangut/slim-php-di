<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Tests;

use Invoker\Invoker;
use Jgut\Slim\PHPDI\CallableStrategy;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 */
class CallableStrategyTest extends TestCase
{
    public function testInvoke(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $request->withAttribute('attribute', 'value');

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $callable = static function (ServerRequestInterface $request, $param): void {
            static::assertEquals('value', $request->getAttribute('attribute'));
            static::assertNull('value', $request->getAttribute('param'));
            static::assertEquals('value', $param);
        };

        $invoker = $this->getMockBuilder(Invoker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('call')
            ->with($callable)
            ->willReturn($response);

        $strategy = new CallableStrategy($invoker);

        $strategy($callable, $request, $response, ['param' => 'value']);
    }

    public function testInvokeAppendingToRequest(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $request->withAttribute('attribute', 'value');

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $callable = static function (ServerRequestInterface $request, $param): void {
            static::assertEquals('value', $request->getAttribute('attribute'));
            static::assertEquals('value', $request->getAttribute('param'));
            static::assertEquals('value', $param);
        };

        $invoker = $this->getMockBuilder(Invoker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(static::once())
            ->method('call')
            ->with($callable)
            ->willReturn($response);

        $strategy = new CallableStrategy($invoker, true);

        $strategy($callable, $request, $response, ['param' => 'value']);
    }
}
