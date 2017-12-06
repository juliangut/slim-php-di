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

use DI\NotFoundException;
use Slim\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * PHP-DI Dependency Injection Container trait.
 */
trait ContainerTrait
{
    /**
     * Returns an entry of the container by its name.
     *
     * @see \DI\Container::get
     *
     * @param string $name
     *
     * @throws ContainerValueNotFoundException
     * @throws ContainerException
     *
     * @return mixed
     */
    public function get($name)
    {
        try {
            if (is_string($name) && strpos($name, 'settings.') === 0) {
                return $this->getSetting(substr($name, 9), parent::get('settings'));
            }

            return parent::get($name);
        } catch (NotFoundException $exception) {
            throw new ContainerValueNotFoundException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (\Exception $exception) {
            throw new ContainerException($exception->getMessage(), $exception->getCode(), $exception);
        }
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
     * @return mixed
     */
    public function has($name)
    {
        if (is_string($name) && strpos($name, 'settings.') === 0) {
            try {
                $this->getSetting(substr($name, 9), parent::get('settings'));

                return true;
            } catch (\Exception $exception) {
                return false;
            }
        }

        return parent::has($name);
    }

    /**
     * Get setting from settings.
     *
     * @param string $setting
     * @param array  $settings
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    protected function getSetting(string $setting, array $settings)
    {
        $segments = explode('.', $setting);

        while ($segment = array_shift($segments)) {
            if (count($segments) > 0) {
                $combinedSetting = $segment . '.' . implode('.', $segments);
                if (is_array($settings) && array_key_exists($combinedSetting, $settings)) {
                    return $settings[$combinedSetting];
                }
            }

            if (!is_array($settings) || !array_key_exists($segment, $settings)) {
                throw new NotFoundException(sprintf('Setting "%s" not found', $setting));
            }

            $settings = $settings[$segment];
        }

        return $settings;
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
     * @throws \RuntimeException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($name)
    {
        throw new \RuntimeException('It is not possible to unset container definitions');
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
     *
     * @throws \RuntimeException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __unset(string $name)
    {
        throw new \RuntimeException('It is not possible to unset container definitions');
    }
}
