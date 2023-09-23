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

use InvalidArgumentException;
use Invoker\CallableResolver as InvokerResolver;
use Invoker\Exception\NotCallableException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\AdvancedCallableResolverInterface;

class CallableResolver implements AdvancedCallableResolverInterface
{
    /** @see https://regex101.com/r/lDdngD/1 */
    protected const CALLABLE_PATTERN
        = '!^(?P<class>[^\:]+)\:{1,2}(?P<method>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    public function __construct(
        private readonly InvokerResolver $callableResolver,
    ) {}

    /**
     * @param string|callable(): mixed $toResolve
     *
     * @throws InvalidArgumentException
     *
     * @return callable(): ResponseInterface
     */
    public function resolve($toResolve): callable
    {
        $resolvable = $toResolve;

        if (\is_string($resolvable)) {
            $resolvable = $this->callableFromStringNotation($resolvable);
        }

        return $this->resolveCallable($resolvable, $toResolve);
    }

    /**
     * @param string|callable(): mixed|object $toResolve
     *
     * @throws InvalidArgumentException
     *
     * @return callable(): ResponseInterface
     */
    public function resolveRoute($toResolve): callable
    {
        $resolvable = $toResolve;

        if (\is_string($resolvable)) {
            $resolvable = $this->callableFromStringNotation($resolvable, 'handle');
        }
        if (\is_object($resolvable)) {
            if (!$resolvable instanceof RequestHandlerInterface) {
                throw new InvalidArgumentException(
                    sprintf('Route class should implement "%s".', RequestHandlerInterface::class),
                );
            }

            /** @var callable():mixed $resolvable */
            $resolvable = [$resolvable, 'handle'];
        }

        return $this->resolveCallable($resolvable, $toResolve);
    }

    /**
     * @param string|callable(): mixed|object $toResolve
     *
     * @throws InvalidArgumentException
     *
     * @return callable(): ResponseInterface
     */
    public function resolveMiddleware($toResolve): callable
    {
        $resolvable = $toResolve;

        if (\is_string($resolvable)) {
            $resolvable = $this->callableFromStringNotation($resolvable, 'process');
        }
        if (\is_object($resolvable)) {
            if (!$resolvable instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(
                    sprintf('Middleware class should implement "%s".', MiddlewareInterface::class),
                );
            }

            /** @var callable():mixed $resolvable */
            $resolvable = [$resolvable, 'process'];
        }

        return $this->resolveCallable($resolvable, $toResolve);
    }

    /**
     * Get resolved callable.
     *
     * @param string|callable(): mixed|array<string>        $resolvable
     * @param string|callable(): mixed|array<string>|object $toResolve
     *
     * @throws InvalidArgumentException
     *
     * @return callable(): ResponseInterface
     */
    protected function resolveCallable(
        string|callable|array $resolvable,
        string|callable|array|object $toResolve,
    ): callable {
        try {
            return $this->callableResolver->resolve($resolvable);
        } catch (NotCallableException $exception) {
            if (\is_callable($toResolve) || \is_array($toResolve)) {
                $callable = json_encode($toResolve, \JSON_THROW_ON_ERROR);
            } elseif (\is_object($toResolve)) {
                $callable = $toResolve::class;
            } else {
                $callable = $toResolve;
            }

            throw new InvalidArgumentException(sprintf('"%s" is not resolvable.', $callable), 0, $exception);
        }
    }

    /**
     * Get callable from string callable notation.
     *
     * @return string|callable(): mixed|list<string>
     */
    private function callableFromStringNotation(string $toResolve, ?string $defaultMethod = null): string|callable|array
    {
        $callable = $toResolve;

        if (preg_match(self::CALLABLE_PATTERN, $toResolve, $matches) === 1) {
            /** @var callable(): mixed $callable */
            $callable = [$matches['class'], $matches['method']];
        } elseif ($defaultMethod !== null) {
            /** @var callable(): mixed $callable */
            $callable = [$toResolve, $defaultMethod];
        }

        return $callable;
    }
}
