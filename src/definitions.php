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

use Invoker\CallableResolver as InvokerCallableResolver;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Jgut\Slim\PHPDI\CallableResolver;
use Jgut\Slim\PHPDI\CallableStrategy;
use Jgut\Slim\PHPDI\Configuration;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\Psr17\Psr17FactoryProvider;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Interfaces\ServerRequestCreatorInterface;
use Slim\MiddlewareDispatcher;
use Slim\Routing\Dispatcher;
use Slim\Routing\RouteCollector;
use Slim\Routing\RouteResolver;
use Slim\Routing\RouteRunner;

return [
    // Replaced by used configuration
    Configuration::class => null,

    // Replaced by container itself
    ContainerInterface::class => null,

    ServerRequestCreatorInterface::class => static function (): ServerRequestCreatorInterface {
        return ServerRequestCreatorFactory::create();
    },
    ResponseFactoryInterface::class => static function (): ResponseFactoryInterface {
        return AppFactory::determineResponseFactory();
    },
    StreamFactoryInterface::class => static function (): StreamFactoryInterface {
        /** @var \Slim\Factory\Psr17\Psr17Factory $psr17factory */
        foreach (Psr17FactoryProvider::getFactories() as $psr17factory) {
            if ($psr17factory::isStreamFactoryAvailable()) {
                return $psr17factory::getStreamFactory();
            }
        }

        throw new RuntimeException('Could not detect any PSR-17 StreamFactory implementation.');
    },

    CallableResolverInterface::class => static function (ContainerInterface $container): CallableResolverInterface {
        return new CallableResolver(new InvokerCallableResolver($container));
    },

    InvocationStrategyInterface::class => static function (ContainerInterface $container): InvocationStrategyInterface {
        $resolveChain = new ResolverChain([
            // Inject parameters by name first
            new AssociativeArrayResolver(),
            // Then inject services by type-hints for those that weren't resolved
            new TypeHintContainerResolver($container),
            // Then fall back on parameters default values for optional route parameters
            new DefaultValueResolver(),
        ]);

        return new CallableStrategy(new Invoker($resolveChain, $container));
    },

    RouteCollectorInterface::class => static function (ContainerInterface $container): RouteCollectorInterface {
        return new RouteCollector(
            $container->get(ResponseFactoryInterface::class),
            $container->get(CallableResolverInterface::class),
            $container,
            $container->get(InvocationStrategyInterface::class)
        );
    },

    DispatcherInterface::class => static function (ContainerInterface $container): DispatcherInterface {
        return new Dispatcher($container->get(RouteCollectorInterface::class));
    },

    RouteResolverInterface::class => static function (ContainerInterface $container): RouteResolverInterface {
        return new RouteResolver(
            $container->get(RouteCollectorInterface::class),
            $container->get(DispatcherInterface::class)
        );
    },

    MiddlewareDispatcherInterface::class => static function (
        ContainerInterface $container
    ): MiddlewareDispatcherInterface {
        $requestHandler = new class() implements RequestHandlerInterface {
            /**
             * {@inheritdoc}
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // @codeCoverageIgnoreStart
                throw new  \RuntimeException(\sprintf('This RequestHandler is replaced by "%s".', RouteRunner::class));
                // @codeCoverageIgnoreEnd
            }
        };

        return new MiddlewareDispatcher(
            $requestHandler,
            $container->get(CallableResolverInterface::class),
            $container
        );
    },

    App::class => static function (ContainerInterface $container): App {
        return new App(
            $container->get(ResponseFactoryInterface::class),
            $container,
            $container->get(CallableResolverInterface::class),
            $container->get(RouteCollectorInterface::class),
            $container->get(RouteResolverInterface::class),
            $container->get(MiddlewareDispatcherInterface::class)
        );
    },
];
