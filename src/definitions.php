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
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\FoundHandler;
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
use Slim\Interfaces\InvocationStrategyInterface;
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
    ServerRequestInterface::class => function (ContainerInterface $container) {
        return Request::createFromEnvironment($container->get('environment'));
    },
    'request' => \DI\get(ServerRequestInterface::class),
    ResponseInterface::class => function (ContainerInterface $container) {
        $headers = new Headers(['Content-Type' => 'text/html; charset=utf-8']);
        $response = new Response(200, $headers);

        return $response->withProtocolVersion($container->get('settings')['httpVersion']);
    },
    'response' => \DI\get(ResponseInterface::class),

    Router::class => function (ContainerInterface $container): Router {
        $router = new Router();

        $router->setCacheFile($container->get('settings.routerCacheFile'));
        $router->setContainer($container);

        return $router;
    },
    'router' => \DI\get(Router::class),

    'phpErrorHandler' => \DI\create(PhpError::class)
        ->constructor(\DI\get('settings.displayErrorDetails')),
    'errorHandler' => \DI\create(Error::class)
        ->constructor(\DI\get('settings.displayErrorDetails')),
    'notFoundHandler' => \DI\create(NotFound::class),
    'notAllowedHandler' => \DI\create(NotAllowed::class),

    InvocationStrategyInterface::class => function (ContainerInterface $container) {
        $resolveChain = new ResolverChain([
            // Inject parameters by name first
            new AssociativeArrayResolver(),
            // Then inject services by type-hints for those that weren't resolved
            new TypeHintContainerResolver($container),
            // Then fall back on parameters default values for optional route parameters
            new DefaultValueResolver(),
        ]);

        return new FoundHandler(new Invoker($resolveChain, $container));
    },
    'foundHandler' => \DI\get(InvocationStrategyInterface::class),

    'callableResolver' => function (ContainerInterface $container) {
        return new CallableResolver(new InvokerResolver($container));
    },

    // Replaced by used configuration on container build
    Configuration::class => null,

    // Replaced by generated container
    ContainerInterface::class => null,
];
