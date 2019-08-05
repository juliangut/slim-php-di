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

use Invoker\CallableResolver as InvokerCallableResolver;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory as SlimAppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;

/**
 * Custom PHP-DI aware AppFactory.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AppFactory extends SlimAppFactory
{
    /**
     * @var Configuration
     */
    protected static $configuration;

    /**
     * {@inheritdoc}
     */
    public static function create(
        ?ResponseFactoryInterface $responseFactory = null,
        ?ContainerInterface $container = null,
        ?CallableResolverInterface $callableResolver = null,
        ?RouteCollectorInterface $routeCollector = null,
        ?RouteResolverInterface $routeResolver = null
    ): App {
        $container = $container ?? ContainerBuilder::build(static::$configuration);

        $app = parent::create(
            $responseFactory,
            $container,
            $callableResolver ?? new CallableResolver(new InvokerCallableResolver($container)),
            $routeCollector,
            $routeResolver
        );

        $app
            ->getRouteCollector()
            ->setDefaultInvocationStrategy(static::getInvocationStrategy($container));

        return $app;
    }

    /**
     * Get custom invocation strategy.
     *
     * @param ContainerInterface $container
     *
     * @return InvocationStrategyInterface
     */
    final protected static function getInvocationStrategy(ContainerInterface $container): InvocationStrategyInterface
    {
        $resolveChain = new ResolverChain([
            // Inject parameters by name first
            new AssociativeArrayResolver(),
            // Then inject services by type-hints for those that weren't resolved
            new TypeHintContainerResolver($container),
            // Then fall back on parameters default values for optional route parameters
            new DefaultValueResolver(),
        ]);

        return new CallableStrategy(new Invoker($resolveChain, $container));
    }

    /**
     * Set container building configurations.
     *
     * @param Configuration $configuration
     */
    final public static function setContainerConfiguration(Configuration $configuration): void
    {
        static::$configuration = $configuration;
    }
}
