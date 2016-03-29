<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 *
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI;

use DI\ContainerBuilder as DIContainerBuilder;
use Interop\Container\ContainerInterface;
use Slim\CallableResolver;
use Slim\Collection;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Handlers\Error;
use Slim\Handlers\NotFound;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\PhpError;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Router;

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
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
    ];

    /**
     * Build PHP-DI container for Slim Framework.
     *
     * @param array $values      parameter objects
     * @param array $definitions definitions for PHP-DI
     *
     * @return \Jgut\Slim\PHPDI\Container
     */
    public static function build(array $values = [], array $definitions = [])
    {
        $containerBuilder = new DIContainerBuilder('\Jgut\Slim\PHPDI\Container');

        $userSettings = [];
        if (isset($values['settings'])) {
            $userSettings = $values['settings'];

            unset($values['settings']);
        }

        if (isset($userSettings['php-di']) && is_array($userSettings['php-di'])) {
            $containerBuilder = self::configureContainerBuilder($containerBuilder, $userSettings['php-di']);
            $containerBuilder = self::configureContainerProxies($containerBuilder, $userSettings['php-di']);
            $containerBuilder = self::configureContainerCache($containerBuilder, $userSettings['php-di']);
        }

        // Add default services definitions
        $containerBuilder->addDefinitions(self::getDefaultServicesDefinitions($userSettings));

        // Add settings services definitions
        $containerBuilder->addDefinitions($values);

        // Add custom service definitions
        $containerBuilder->addDefinitions($definitions);

        return $containerBuilder->build();
    }

    /**
     * Configure container builder.
     *
     * @param \DI\ContainerBuilder $containerBuilder
     * @param array                $settings
     *
     * @return \DI\ContainerBuilder
     */
    private static function configureContainerBuilder(DIContainerBuilder $containerBuilder, array $settings)
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

        return $containerBuilder;
    }

    /**
     * Configure container's proxies.
     *
     * @param \DI\ContainerBuilder $containerBuilder
     * @param array                $settings
     *
     * @return \DI\ContainerBuilder
     */
    private static function configureContainerProxies(DIContainerBuilder $containerBuilder, array $settings)
    {
        if (isset($settings['proxy_path']) && !empty($settings['proxy_path'])) {
            $containerBuilder->writeProxiesToFile(true, $settings['proxy_path']);
        }

        return $containerBuilder;
    }

    /**
     * Configure container's cache system.
     *
     * @param \DI\ContainerBuilder $containerBuilder
     * @param array                $settings
     *
     * @return \DI\ContainerBuilder
     */
    private static function configureContainerCache(DIContainerBuilder $containerBuilder, array $settings)
    {
        if (isset($settings['definitions_cache'])) {
            $containerBuilder->setDefinitionCache($settings['definitions_cache']);
        }

        return $containerBuilder;
    }

    /**
     * Get definitions for Slim's default services
     *
     * @param array $userSettings
     *
     * @return callable[]
     */
    private static function getDefaultServicesDefinitions(array $userSettings)
    {
        $defaultSettings = self::$defaultSettings;

        return [
            'settings' => function () use ($defaultSettings, $userSettings) {
                return new Collection(array_merge($defaultSettings, $userSettings));
            },

            'environment' => function () {
                return new Environment($_SERVER);
            },

            'request' => function (ContainerInterface $container) {
                return Request::createFromEnvironment($container->get('environment'));
            },

            'response' => function (ContainerInterface $container) {
                $headers = new Headers(['Content-Type' => 'text/html; charset=utf-8']);
                $response = new Response(200, $headers);

                return $response->withProtocolVersion($container->get('settings')['httpVersion']);
            },

            'router' => function () {
                return new Router;
            },

            'foundHandler' => function () {
                return new RequestResponse;
            },

            'phpErrorHandler' => function(ContainerInterface $container) {
                return new PhpError($container->get('settings')['displayErrorDetails']);
            },

            'errorHandler' => function (ContainerInterface $container) {
                return new Error($container->get('settings')['displayErrorDetails']);
            },

            'notFoundHandler' => function () {
                return new NotFound;
            },

            'notAllowedHandler' => function () {
                return new NotAllowed;
            },

            'callableResolver' => function (ContainerInterface $container) {
                return new CallableResolver($container);
            },
        ];
    }
}
