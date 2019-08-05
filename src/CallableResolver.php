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
use Slim\Interfaces\CallableResolverInterface;

/**
 * Resolve middleware and route callables using PHP-DI.
 */
class CallableResolver implements CallableResolverInterface
{
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
     */
    public function resolve($toResolve): callable
    {
        /** @var mixed $resolved */
        $resolved = $this->callableResolver->resolve($toResolve);

        if (!\is_callable($resolved)) {
            throw new \RuntimeException(\sprintf(
                '"%s" is not resolvable',
                \is_string($toResolve) ? $toResolve : \gettype($toResolve)
            ));
        }

        return $resolved;
    }
}
