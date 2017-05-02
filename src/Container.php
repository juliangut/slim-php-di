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

use DI\Container as DIContainer;
use DI\NotFoundException;
use Slim\Exception\ContainerException;
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
     * @throws ContainerValueNotFoundException
     *
     * @return mixed
     */
    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (NotFoundException $exception) {
            throw new ContainerValueNotFoundException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (\Exception $exception) {
            throw new ContainerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Define an object or a value in the container.
     *
     * @see \DI\Container::set
     *
     * @param string $name
     * @param mixed  $value
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

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
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @see \DI\Container::has
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Unset a container entry by its name.
     *
     * @param string $name
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($name)
    {
        // Can't remove definitions from $this->definitionSource as it is a private attribute
        // Can't manually remove services as $this->singletonEntries is a private attribute
    }

    /**
     * @see \DI\Container::get
     *
     * @param string $name
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @see \DI\Container::set
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @see \DI\Container::has
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * @see \Jgut\Slim\PHPDI\Container::offset
     *
     * @param string $name
     */
    public function __unset(string $name)
    {
        $this->offsetUnset($name);
    }
}
