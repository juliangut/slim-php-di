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

use Invoker\Invoker;
use Jgut\Slim\PHPDI\FoundHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * FoundHandler tests.
 */
class FoundHanlerTest extends \PHPUnit_Framework_TestCase
{
    public function testInvokable()
    {
        $callable = function () {
            // Empty
        };
        $parameters = [
            'param' => 'value',
        ];

        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var ServerRequestInterface $request */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var ResponseInterface $response */

        $invoker = $this->getMockBuilder(Invoker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(self::once())
            ->method('call')
            ->with($callable, array_merge(['request' => $request, 'response' => $response], $parameters));
        /* @var \DI\InvokerInterface $invoker */

        $handler = new FoundHandler($invoker);

        $handler($callable, $request, $response, $parameters);
    }
}
