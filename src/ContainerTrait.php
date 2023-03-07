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

use DI\DependencyException;
use DI\NotFoundException;
use InvalidArgumentException;
use ReturnTypeWillChange;
use RuntimeException;
use Throwable;

/**
 * @template T of object
 */
trait ContainerTrait
{
    /**
     * @see \DI\Container::get
     *
     * @param string|class-string<T> $name
     *
     * @throws DependencyException
     * @throws NotFoundException
     *
     * @return mixed|T
     */
    public function get(string $name): mixed
    {
        try {
            return mb_strpos($name, '.') === false
                ? parent::get($name)
                : $this->getRecursive($name);
        } catch (NotFoundException $exception) {
            throw new NotFoundException(
                sprintf('No entry or class found for "%s".', $name),
                $exception->getCode(),
                $exception,
            );
        } catch (Throwable $exception) {
            throw new DependencyException(
                rtrim($exception->getMessage(), '.') . '.',
                $exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * @see \DI\Container::has
     *
     * @param string|class-string<T> $name
     *
     * @throws InvalidArgumentException
     */
    public function has(string $name): bool
    {
        if (mb_strpos($name, '.') === false) {
            return parent::has($name);
        }

        try {
            $this->getRecursive($name);
            // @phpstan-ignore-next-line
        } catch (Throwable $exception) {
            // @ignoreException
            return false;
        }

        return true;
    }

    /**
     * @param string|class-string<T> $key
     * @param array<mixed>|null      $parent
     *
     * @throws NotFoundException
     *
     * @return mixed|T
     */
    private function getRecursive(string $key, ?array $parent = null)
    {
        if ($parent !== null ? \array_key_exists($key, $parent) : parent::has($key)) {
            return $parent !== null ? $parent[$key] : parent::get($key);
        }

        $keySegments = explode('.', $key);
        $keyParts = [];

        while (\count($keySegments) > 1) {
            array_unshift($keyParts, array_pop($keySegments));
            $subKey = implode('.', $keySegments);

            if ($parent !== null ? \array_key_exists($subKey, $parent) : parent::has($subKey)) {
                $parent = $parent !== null ? $parent[$subKey] : parent::get($subKey);

                if (!\is_array($parent)) {
                    break;
                }

                return $this->getRecursive(implode('.', $keyParts), $parent);
            }
        }

        throw new NotFoundException(sprintf('Entry "%s" not found.', $key));
    }

    /**
     * Define an object or a value in the container.
     *
     * @see \DI\Container::set
     *
     * @param mixed|string|class-string<T> $name
     * @param mixed|T                      $value
     *
     * @deprecated since 3.0
     */
    public function offsetSet($name, $value): void
    {
        @trigger_error(
            'ArrayAccess is deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        /** @var string $name */
        $this->set($name, $value);
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @see \DI\Container::get
     *
     * @param mixed|string|class-string<T> $name
     *
     * @phpstan-return mixed
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    #[ReturnTypeWillChange]
    public function offsetGet($name)
    {
        @trigger_error(
            'ArrayAccess is deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        /** @var string|class-string<T> $name */
        return $this->get($name);
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @see \DI\Container::has
     *
     * @param mixed|string|class-string<T> $name
     *
     * @throws InvalidArgumentException
     */
    public function offsetExists($name): bool
    {
        @trigger_error(
            'ArrayAccess is deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        /** @var string|class-string<T> $name */
        return $this->has($name);
    }

    /**
     * Unset a container entry by its name.
     *
     * @param mixed|string|class-string<T> $name
     *
     * @throws RuntimeException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($name): void
    {
        @trigger_error(
            'ArrayAccess is deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        throw new RuntimeException('It is not possible to unset a container definitions.');
    }

    /**
     * @see \DI\Container::set
     *
     * @param string|class-string<T> $name
     * @param mixed|T                $value
     */
    public function __set(string $name, $value): void
    {
        @trigger_error(
            'Magic methods are deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        $this->set($name, $value);
    }

    /**
     * @see \DI\Container::get
     *
     * @param string|class-string<T> $name
     *
     * @throws DependencyException
     * @throws NotFoundException
     *
     * @return mixed|T
     */
    public function __get(string $name)
    {
        @trigger_error(
            'Magic methods are deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        return $this->get($name);
    }

    /**
     * @see \DI\Container::has
     */
    public function __isset(string $name): bool
    {
        @trigger_error(
            'Magic methods are deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        return $this->has($name);
    }

    /**
     * @see \Jgut\Slim\PHPDI\Container::offset
     *
     * @throws RuntimeException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __unset(string $name): void
    {
        @trigger_error(
            'Magic methods are deprecated since 3.0 and will be removed in 4.0. Use PSR-11 and PHP-DI methods instead.',
            \E_USER_DEPRECATED,
        );

        throw new RuntimeException('It is not possible to unset a container definitions.');
    }
}
