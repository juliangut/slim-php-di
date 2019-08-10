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

/**
 * Route callback strategy with PHP-DI.
 */
class CallableStrategy implements InvocationStrategyInterface
{
    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @var bool
     */
    protected $appendRouteArguments;

    /**
     * CallableStrategy constructor.
     *
     * @param InvokerInterface $invoker
     * @param bool             $appendRouteArguments
     */
    public function __construct(InvokerInterface $invoker, bool $appendRouteArguments = false)
    {
        $this->invoker = $invoker;
        $this->appendRouteArguments = $appendRouteArguments;
    }

    /**
     * Invoke a route callable.
     *
     * @param callable               $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $routeArguments
     *
     * @return ResponseInterface
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        if ($this->appendRouteArguments) {
            foreach ($routeArguments as $k => $v) {
                $request = $request->withAttribute($k, $v);
            }
        }

        // Inject the request and response by parameter name
        $parameters = [
            'request' => $request,
            'response' => $response,
        ];

        if (!$this->appendRouteArguments) {
            // Inject the route arguments by name
            $parameters += $routeArguments;
        }

        // Inject the attributes defined on the request
        $parameters += $request->getAttributes();

        return $this->invoker->call($callable, $parameters);
    }
}
