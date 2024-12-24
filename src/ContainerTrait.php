<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI;

use DI\DependencyException;
use DI\NotFoundException;
use InvalidArgumentException;
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
            return !str_contains($name, '.')
                ? parent::get($name)
                : $this->getRecursive($name);
        } catch (NotFoundException $exception) {
            throw new NotFoundException(
                \sprintf('No entry or class found for "%s".', $name),
                $exception->getCode(),
                $exception,
            );
        } catch (Throwable $exception) {
            throw new DependencyException(
                rtrim($exception->getMessage(), '.') . '.',
                (int) $exception->getCode(),
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
        if (!str_contains($name, '.')) {
            return parent::has($name);
        }

        try {
            $this->getRecursive($name);
        } catch (Throwable) { // @phpstan-ignore-line
            // @ignoreException
            return false;
        }

        return true;
    }

    /**
     * @param string|class-string<T>                                 $key
     * @param array<int|string, mixed|array<int|string, mixed>>|null $parent
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

                /** @var array<int|string, mixed> $parent */
                return $this->getRecursive(implode('.', $keyParts), $parent);
            }
        }

        throw new NotFoundException(\sprintf('Entry "%s" not found.', $key));
    }
}
