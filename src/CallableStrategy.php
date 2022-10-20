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

class CallableStrategy implements InvocationStrategyInterface
{
    private InvokerInterface $invoker;

    protected bool $appendRouteArguments;

    public function __construct(InvokerInterface $invoker, bool $appendRouteArguments = false)
    {
        $this->invoker = $invoker;
        $this->appendRouteArguments = $appendRouteArguments;
    }

    /**
     * @param array<mixed>      $routeArguments
     * @param callable(): mixed $callable
     *
     * @throws InvalidCallableResponse
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {
        if ($this->appendRouteArguments) {
            foreach ($routeArguments as $argument => $value) {
                $request = $request->withAttribute($argument, $value);
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

        $invocationResponse = $this->invoker->call($callable, $parameters);
        if (!$invocationResponse instanceof ResponseInterface) {
            throw new InvalidCallableResponse(sprintf(
                'Response should be an instance of "%s", "%s" returned.',
                ResponseInterface::class,
                \is_object($invocationResponse) ? \get_class($invocationResponse) : \gettype($invocationResponse),
            ));
        }

        return $invocationResponse;
    }
}
