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

namespace Jgut\Slim\PHPDI;

use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class FoundHandler implements InvocationStrategyInterface
{
    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * ControllerInvoker constructor.
     *
     * @param InvokerInterface $invoker
     */
    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Invoke a route callable.
     *
     * @param callable               $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $routeArguments
     *
     * @return ResponseInterface|string
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ) {
        // Inject the request and response by parameter name
        $parameters = [
            'request' => $request,
            'response' => $response,
        ];

        // Inject the route arguments by name
        $parameters += $routeArguments;

        return $this->invoker->call($callable, $parameters);
    }
}
