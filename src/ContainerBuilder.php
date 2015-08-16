<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI;

use DI\ContainerBuilder as DIContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Router;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Handlers\Error;
use Slim\Handlers\NotFound;
use Slim\Handlers\NotAllowed;
use Slim\CallableResolver;

/**
 * Helper to create and configure a Container.
 *
 * Default Slim services are included in the generated container.
 */
class ContainerBuilder
{
    /**
     * Slim default settings
     *
     * @var array
     */
    protected static $defaultSettings = [
        'cookieLifetime' => '20 minutes',
        'cookiePath' => '/',
        'cookieDomain' => null,
        'cookieSecure' => false,
        'cookieHttpOnly' => false,
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
    ];

    /**
     * Build PHP-DI container for Slim Framework.
     *
     * @param array $userSettings user settings for Slim
     * @param array $definitions PHP definitions for PHP-DI
     *
     * @return \Jgut\Slim\Container
     */
    public static function build(array $userSettings = [], array $definitions = [])
    {
        $containerBuilder = new DIContainerBuilder('\Jgut\Slim\PHPDI\Container');

        if (isset($userSettings['php-di']) && is_array($userSettings['php-di'])) {
            $containerBuilder = self::configureContainerBuilder($containerBuilder, $userSettings['php-di']);

            $containerBuilder = self::configureContainerCache($containerBuilder, $userSettings['php-di']);

            $definitions = self::getDefinitions($userSettings['php-di'], $definitions);
        }

        $containerBuilder->addDefinitions(self::getDefaultServicesDefinitions($userSettings));

        $containerBuilder->addDefinitions($definitions);

        $container = $containerBuilder->build();

        return $container;
    }

    /**
     * Configure container builder.
     *
     * @param DI\ContainerBuilder $containerBuilder
     * @param array $settings
     *
     * @return DI\ContainerBuilder
     */
    protected static function configureContainerBuilder(DIContainerBuilder $containerBuilder, array $settings)
    {
        if (isset($settings['use_autowiring'])) {
            $containerBuilder->useAutowiring((bool) $settings['use_autowiring']);
        }

        if (isset($settings['use_annotations'])) {
            $containerBuilder->useAnnotations((bool) $settings['use_annotations']);
        }

        if (isset($settings['ignore_phpdoc_errors'])) {
            $containerBuilder->ignorePhpDocErrors((bool) $settings['ignore_phpdoc_errors']);
        }

        if (isset($settings['proxy_path']) && !empty($settings['proxy_path'])) {
            $containerBuilder->writeProxiesToFile(true, $settings['proxy_path']);
        }

        return $containerBuilder;
    }

    /**
     * Configure container's cache system.
     *
     * @param DI\ContainerBuilder $containerBuilder
     * @param array $settings
     *
     * @return DI\ContainerBuilder
     */
    protected static function configureContainerCache(DIContainerBuilder $containerBuilder, array $settings)
    {
        if (isset($settings['definitions_cache'])) {
            $containerBuilder->setDefinitionCache($settings['definitions_cache']);
        }

        return $containerBuilder;
    }

    /**
     * Unify definitions.
     *
     * @param array $settings
     * @param array $definitions
     *
     * @return array
     */
    protected static function getDefinitions(array $settings, array $definitions)
    {
        if (isset($settings['definitions']) && is_array($settings['definitions'])) {
            $definitions = array_merge($settings['definitions'], $definitions);
        }

        return $definitions;
    }

    /**
     * Get definitions for Slim's default services
     *
     * @param array $settings
     *
     * @return array
     */
    protected static function getDefaultServicesDefinitions(array $settings)
    {
        $defaultSettings = self::$defaultSettings;

        return [
            /**
             * This service MUST return an array or an
             * instance of \ArrayAccess.
             *
             * @return array|\ArrayAccess
             */
            'settings' => function () use ($defaultSettings, $settings) {
                return array_merge($defaultSettings, $settings);
            },

            /**
             * This service MUST return a shared instance
             * of \Slim\Interfaces\Http\EnvironmentInterface.
             *
             * @return \Slim\Interfaces\Http\EnvironmentInterface
             */
            'environment' => function () {
                return new Environment($_SERVER);
            },

            /**
             * PSR-7 Request object
             *
             * @param Interop\Container\ContainerInterface $container
             *
             * @return \Psr\Http\Message\ServerRequestInterface
             */
            'request' => function (ContainerInterface $container) {
                return Request::createFromEnvironment($container['environment']);
            },

            /**
             * PSR-7 Response object
             *
             * @param Interop\Container\ContainerInterface $container
             *
             * @return \Psr\Http\Message\ResponseInterface
             */
            'response' => function (ContainerInterface $container) {
                $headers = new Headers(['Content-Type' => 'text/html']);
                $response = new Response(200, $headers);

                return $response->withProtocolVersion($container['settings']['httpVersion']);
            },

            /**
             * This service MUST return a SHARED instance
             * of \Slim\Interfaces\RouterInterface.
             *
             * @return \Slim\Interfaces\RouterInterface
             */
            'router' => function () {
                return new Router;
            },

            /**
             * This service MUST return a SHARED instance
             * of \Slim\Interfaces\InvocationStrategyInterface.
             *
             * @return \Slim\Interfaces\InvocationStrategyInterface
             */
            'foundHandler' => function () {
                return new RequestResponse;
            },

            /**
             * This service MUST return a callable
             * that accepts three arguments:
             *
             * 1. Instance of \Psr\Http\Message\ServerRequestInterface
             * 2. Instance of \Psr\Http\Message\ResponseInterface
             * 3. Instance of \Exception
             *
             * The callable MUST return an instance of
             * \Psr\Http\Message\ResponseInterface.
             *
             * @return callable
             */
            'errorHandler' => function () {
                return new Error;
            },

            /**
             * This service MUST return a callable
             * that accepts two arguments:
             *
             * 1. Instance of \Psr\Http\Message\ServerRequestInterface
             * 2. Instance of \Psr\Http\Message\ResponseInterface
             *
             * The callable MUST return an instance of
             * \Psr\Http\Message\ResponseInterface.
             *
             * @return callable
             */
            'notFoundHandler' => function () {
                return new NotFound;
            },

            /**
             * This service MUST return a callable
             * that accepts three arguments:
             *
             * 1. Instance of \Psr\Http\Message\ServerRequestInterface
             * 2. Instance of \Psr\Http\Message\ResponseInterface
             * 3. Array of allowed HTTP methods
             *
             * The callable MUST return an instance of
             * \Psr\Http\Message\ResponseInterface.
             *
             * @return callable
             */
            'notAllowedHandler' => function () {
                return new NotAllowed;
            },

            /**
             * Instance of \Slim\Interfaces\CallableResolverInterface
             *
             * @param Interop\Container\ContainerInterface $container
             *
             * @return \Slim\Interfaces\CallableResolverInterface
             */
            'callableResolver' => function (ContainerInterface $container) {
                return new CallableResolver($container);
            },
        ];
    }
}
