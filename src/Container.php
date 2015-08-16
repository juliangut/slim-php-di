<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI;

use DI\Container as DIContainer;
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
use Slim\Exception\NotFoundException as SlimNotFoundException;

/**
 * PHP-DI Dependency Injection Slim integration.
 *
 * Slim\App expects a container that implements Interop\Container\ContainerInterface
 * with these service keys configured and ready for use:
 *
 *  - settings: an array or instance of \ArrayAccess
 *  - environment: an instance of \Slim\Interfaces\Http\EnvironmentInterface
 *  - request: an instance of \Psr\Http\Message\ServerRequestInterface
 *  - response: an instance of \Psr\Http\Message\ResponseInterface
 *  - router: an instance of \Slim\Interfaces\RouterInterface
 *  - foundHandler: an instance of \Slim\Interfaces\InvocationStrategyInterface
 *  - errorHandler: a callable with the signature: function($request, $response, $exception)
 *  - notFoundHandler: a callable with the signature: function($request, $response)
 *  - notAllowedHandler: a callable with the signature: function($request, $response, $allowedHttpMethods)
 *  - callableResolver: an instance of \Slim\Interfaces\CallableResolverInterface
 */
class Container extends DIContainer implements \ArrayAccess
{
    /**
     * Default settings
     *
     * @var array
     */
    private $defaultSettings = [
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
     * This function registers the default services that Slim needs to work.
     *
     * All services are shared - that is, they are registered such that the
     * same instance is returned on subsequent calls.
     *
     * @param array $userSettings Associative array of application settings
     *
     * @return void
     */
    public function registerDefaultServices(array $userSettings = [])
    {
        $defaultSettings = $this->defaultSettings;

        /**
         * This service MUST return an array or an
         * instance of \ArrayAccess.
         *
         * @param Container $container
         *
         * @return array|\ArrayAccess
         */
        $this->set('settings', function ($container) use ($userSettings, $defaultSettings) {
            return array_merge($defaultSettings, $userSettings);
        });

        /**
         * This service MUST return a shared instance
         * of \Slim\Interfaces\Http\EnvironmentInterface.
         *
         * @return \Slim\Interfaces\Http\EnvironmentInterface
         */
        $this->set('environment', function () {
            return new Environment($_SERVER);
        });

        /**
         * PSR-7 Request object
         *
         * @param Container $container
         *
         * @return \Psr\Http\Message\ServerRequestInterface
         */
        $this->set('request', function ($container) {
            return Request::createFromEnvironment($container['environment']);
        });

        /**
         * PSR-7 Response object
         *
         * @param Container $container
         *
         * @return \Psr\Http\Message\ResponseInterface
         */
        $this->set('response', function ($container) {
            $headers = new Headers(['Content-Type' => 'text/html']);
            $response = new Response(200, $headers);
            return $response->withProtocolVersion($container['settings']['httpVersion']);
        });

        /**
         * This service MUST return a SHARED instance
         * of \Slim\Interfaces\RouterInterface.
         *
         * @return \Slim\Interfaces\RouterInterface
         */
        $this->set('router', function () {
            return new Router();
        });

        /**
         * This service MUST return a SHARED instance
         * of \Slim\Interfaces\InvocationStrategyInterface.
         *
         * @return \Slim\Interfaces\InvocationStrategyInterface
         */
        $this->set('foundHandler', function () {
            return new RequestResponse();
        });

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
        $this->set('errorHandler', function () {
            return new Error();
        });

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
        $this->set('notFoundHandler', function () {
            return new NotFound();
        });

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
        $this->set('notAllowedHandler', function () {
            return new NotAllowed;
        });

        /**
         * Instance of \Slim\Interfaces\CallableResolverInterface
         *
         * @param Container $container
         *
         * @return \Slim\Interfaces\CallableResolverInterface
         */
        $this->set('callableResolver', function ($container) {
            return new CallableResolver($container);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (\Exception $exception) {
            throw new SlimNotFoundException($exception->getMessage());
        }
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string $name    Entry name
     * @param mixed  $value The value of the parameter or a closure to define an object
     *
     * @see \Di\Container::set
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Entry name or a class name
     *
     * @return mixed The value of the container entry
     *
     * @see \DI\Container::get
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @param string $name Entry name or a class name
     *
     * @return bool
     *
     * @see \DI\Container::has
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Unsets a container entry by its name.
     *
     * @param string $name Entry name or a class name
     */
    public function offsetUnset($name)
    {
        // Can't remove definitions from $this->definitionSource as it is a private attribute
        // Can't manually remove services as $this->singletonEntries is a private attribute

        $name = '';
    }
}
