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

use Invoker\CallableResolver as InvokerResolver;
use Jgut\Slim\PHPDI\CallableResolver;

/**
 * CallableResolver tests.
 */
class CallableResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testInvokable()
    {
        $invoker = $this->getMockBuilder(InvokerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoker->expects(self::once())
            ->method('resolve')
            ->with('Controller::method');
        /* @var InvokerResolver $invoker */

        $resolver = new CallableResolver($invoker);

        $resolver->resolve('Controller::method');
    }
}
