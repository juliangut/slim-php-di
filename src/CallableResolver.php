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

use Invoker\CallableResolver as InvokerResolver;
use Invoker\Exception\NotCallableException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\AdvancedCallableResolverInterface;

/**
 * Resolve middleware and route callables using PHP-DI.
 */
class CallableResolver implements AdvancedCallableResolverInterface
{
    /**
     * @var string
     */
    protected const CALLABLE_PATTERN = '!^([^\:]+)\:{1,2}([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    /**
     * @var InvokerResolver
     */
    private $callableResolver;

    /**
     * CallableResolver constructor.
     *
     * @param InvokerResolver $callableResolver
     */
    public function __construct(InvokerResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
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
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function resolveRoute($toResolve): callable
    {
        $resolvable = $toResolve;

        if (\is_string($resolvable)) {
            $resolvable = $this->callableFromStringNotation($resolvable, 'handle');
        }
        if ($resolvable instanceof RequestHandlerInterface) {
            $resolvable = [$resolvable, 'handle'];
        }

        return $this->resolveCallable($resolvable, $toResolve);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function resolveMiddleware($toResolve): callable
    {
        $resolvable = $toResolve;

        if (\is_string($resolvable)) {
            $resolvable = $this->callableFromStringNotation($resolvable, 'process');
        }
        if ($resolvable instanceof MiddlewareInterface) {
            $resolvable = [$resolvable, 'process'];
        }

        return $this->resolveCallable($resolvable, $toResolve);
    }

    /**
     * Get resolved callable.
     *
     * @param mixed $resolvable
     * @param mixed $toResolve
     *
     * @throws \RuntimeException
     *
     * @return callable
     */
    protected function resolveCallable($resolvable, $toResolve): callable
    {
        try {
            return $this->callableResolver->resolve($resolvable);
        } catch (NotCallableException $exception) {
            if (\is_callable($toResolve) || \is_array($toResolve)) {
                $callable = \json_encode($toResolve);
            } elseif (\is_object($toResolve)) {
                $callable = \get_class($toResolve);
            } else {
                $callable = \is_string($toResolve) ? $toResolve : \gettype($toResolve);
            }

            throw new \RuntimeException(\sprintf('"%s" is not resolvable', $callable), 0, $exception);
        }
    }

    /**
     * Get callable from string callable notation.
     *
     * @param string      $toResolve
     * @param string|null $defaultMethod
     *
     * @return string|string[]
     */
    private function callableFromStringNotation(string $toResolve, ?string $defaultMethod = null)
    {
        if (\preg_match(static::CALLABLE_PATTERN, $toResolve, $matches) === 1) {
            return [$matches[1], $matches[2]];
        }

        return $defaultMethod !== null ? [$toResolve, $defaultMethod] : $toResolve;
    }
}
