<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI;

use DI\Container as DIContainer;
use Slim\Exception\NotFoundException;

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
     * {@inheritDoc}
     */
    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (\Exception $exception) {
            throw new NotFoundException($exception->getMessage());
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
