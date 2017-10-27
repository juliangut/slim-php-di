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

use Invoker\CallableResolver as InvokerResolver;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Jgut\Slim\PHPDI\CallableResolver;
use Jgut\Slim\PHPDI\CallableStrategy;
use Jgut\Slim\PHPDI\Configuration;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Error;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\NotFound;
use Slim\Handlers\PhpError;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Router;

return [
    'settings' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ],

    'environment' => \DI\create(Environment::class)
        ->constructor($_SERVER),
    ServerRequestInterface::class => function (ContainerInterface $container): ServerRequestInterface {
        return Request::createFromEnvironment($container->get('environment'));
    },
    'request' => \DI\get(ServerRequestInterface::class),
    ResponseInterface::class => function (ContainerInterface $container): ResponseInterface {
        $headers = new Headers(['Content-Type' => 'text/html; charset=utf-8']);
        $response = new Response(200, $headers);

        return $response->withProtocolVersion($container->get('settings')['httpVersion']);
    },
    'response' => \DI\get(ResponseInterface::class),

    RouterInterface::class => function (ContainerInterface $container): RouterInterface {
        $router = new Router();

        $router->setCacheFile($container->get('settings.routerCacheFile'));
        $router->setContainer($container);

        return $router;
    },
    'router' => \DI\get(RouterInterface::class),

    'phpErrorHandler' => \DI\create(PhpError::class)
        ->constructor(\DI\get('settings.displayErrorDetails')),
    'errorHandler' => \DI\create(Error::class)
        ->constructor(\DI\get('settings.displayErrorDetails')),
    'notFoundHandler' => \DI\create(NotFound::class),
    'notAllowedHandler' => \DI\create(NotAllowed::class),

    InvocationStrategyInterface::class => function (ContainerInterface $container): InvocationStrategyInterface {
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
    'foundHandler' => \DI\get(InvocationStrategyInterface::class),

    CallableResolverInterface::class => function (ContainerInterface $container): CallableResolverInterface {
        return new CallableResolver(new InvokerResolver($container));
    },
    'callableResolver' => \DI\get(CallableResolverInterface::class),

    // Replaced by used configuration
    Configuration::class => null,

    // Replaced by container itself
    ContainerInterface::class => null,
];
