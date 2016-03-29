<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 *
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI;

use DI\Container as DIContainer;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * PHP-DI Dependency Injection Slim integration.
 * Implements ArrayAccess to accommodate to default Slim container based in Pimple.
 *
 * @see \Slim\Container
 */
class Container extends DIContainer implements \ArrayAccess
{
    /**
     * Returns an entry of the container by its name.
     *
     * @see \DI\Container::get
     *
     * @param string $name
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     *
     * @return mixed
     */
    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (\Exception $exception) {
            throw new ContainerValueNotFoundException($exception->getMessage());
        }
    }

    /**
     * Define an object or a value in the container.
     *
     * @see \Di\Container::set
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @see \Di\Container::get
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @see \DI\Container::has
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Unset a container entry by its name.
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        // Can't remove definitions from $this->definitionSource as it is a private attribute
        // Can't manually remove services as $this->singletonEntries is a private attribute

        unset($offset);
    }

    /**
     * @see \Di\Container::get
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @see \Di\Container::has
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
}
