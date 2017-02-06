<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

use Interop\Container\ContainerInterface;
use Invoker\CallableResolver as InvokerResolver;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Jgut\Slim\PHPDI\CallableResolver;
use Jgut\Slim\PHPDI\Container;
use Jgut\Slim\PHPDI\FoundHandler;
use Slim\Handlers\Error;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\NotFound;
use Slim\Handlers\PhpError;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

return [
    'settings.httpVersion' => '1.1',
    'settings.responseChunkSize' => 4096,
    'settings.outputBuffering' => 'append',
    'settings.determineRouteBeforeAppMiddleware' => false,
    'settings.displayErrorDetails' => false,
    'settings.addContentLengthHeader' => true,
    'settings.routerCacheFile' => false,

    'settings' => [
        'httpVersion' => \DI\get('settings.httpVersion'),
        'responseChunkSize' => \DI\get('settings.responseChunkSize'),
        'outputBuffering' => \DI\get('settings.outputBuffering'),
        'determineRouteBeforeAppMiddleware' => \DI\get('settings.determineRouteBeforeAppMiddleware'),
        'displayErrorDetails' => \DI\get('settings.displayErrorDetails'),
        'addContentLengthHeader' => \DI\get('settings.addContentLengthHeader'),
        'routerCacheFile' => \DI\get('settings.routerCacheFile'),
    ],

    'environment' => \DI\object(Environment::class)
        ->constructor($_SERVER),
    'request' => function (ContainerInterface $container) {
        return Request::createFromEnvironment($container->get('environment'));
    },
    'response' => function (ContainerInterface $container) {
        $headers = new Headers(['Content-Type' => 'text/html; charset=utf-8']);
        $response = new Response(200, $headers);

        return $response->withProtocolVersion($container->get('settings')['httpVersion']);
    },

    'router' => \DI\object(Router::class)
        ->method('setCacheFile', \DI\get('settings.routerCacheFile')),

    'phpErrorHandler' => \DI\object(PhpError::class)
        ->constructor(\DI\get('settings.displayErrorDetails')),
    'errorHandler' => \DI\object(Error::class)
        ->constructor(\DI\get('settings.displayErrorDetails')),
    'notFoundHandler' => \DI\object(NotFound::class),
    'notAllowedHandler' => \DI\object(NotAllowed::class),

    'foundHandler' => function (ContainerInterface $container) {
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

    'callableResolver' => function (ContainerInterface $container) {
        return new CallableResolver(new InvokerResolver($container));
    },

    // Aliases
    ContainerInterface::class => \DI\get(Container::class),
];
