<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI;

use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class CallableStrategy implements InvocationStrategyInterface
{
    public function __construct(
        private InvokerInterface $invoker,
        protected bool $appendRouteArguments = false,
    ) {}

    /**
     * @param callable(): mixed    $callable
     * @param array<string, mixed> $routeArguments
     *
     * @throws InvalidCallableResponse
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments,
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

        // Inject the attributes defined in the request
        $parameters += $request->getAttributes();

        $invocationResponse = $this->invoker->call($callable, $parameters);
        if (!$invocationResponse instanceof ResponseInterface) {
            throw new InvalidCallableResponse(sprintf(
                'Response should be an instance of "%s", "%s" returned.',
                ResponseInterface::class,
                \is_object($invocationResponse) ? $invocationResponse::class : \gettype($invocationResponse),
            ));
        }

        return $invocationResponse;
    }
}
