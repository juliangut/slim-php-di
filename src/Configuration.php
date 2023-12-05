<?php

/*
 * (c) 2015-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI;

use Closure;
use DI\CompiledContainer as DICompiledContainer;
use DI\Container as DIContainer;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Traversable;

/**
 * @SuppressWarnings(PMD.LongVariable)
 */
final class Configuration
{
    /**
     * @var class-string<DIContainer>
     */
    private string $containerClass = Container::class;

    private bool $useAutoWiring = true;

    private bool $useAttributes = false;

    private bool $useDefinitionCache = false;

    private ?ContainerInterface $wrapContainer = null;

    private ?string $proxiesPath = null;

    private ?string $compilationPath = null;

    /**
     * @var class-string<DICompiledContainer>
     */
    private string $compiledContainerClass = AbstractCompiledContainer::class;

    /**
     * @var list<string|array<string, scalar|object|Closure|null>>
     */
    private array $definitions = [];

    /**
     * @param iterable<string, mixed> $configurations
     *
     * @throws InvalidArgumentException
     */
    public function __construct(iterable $configurations = [])
    {
        if ($configurations instanceof Traversable) {
            $configurations = iterator_to_array($configurations);
        }

        $configs = array_keys(get_object_vars($this));

        $unknownParameters = array_diff(array_keys($configurations), $configs);
        if (\count($unknownParameters) > 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'The following configuration parameters are not recognized: %s.',
                    implode(', ', $unknownParameters),
                ),
            );
        }

        foreach ($configs as $config) {
            if (\array_key_exists($config, $configurations)) {
                /** @var callable $callback */
                $callback = [$this, 'set' . ucfirst($config)];

                $callback($configurations[$config]);
            }
        }
    }

    /**
     * Get container class.
     *
     * @return class-string<DIContainer>
     */
    public function getContainerClass(): string
    {
        return $this->containerClass;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function setContainerClass(string $containerClass): self
    {
        if (
            !class_exists($containerClass)
            || (
                $containerClass !== DIContainer::class
                && !is_subclass_of($containerClass, DIContainer::class)
            )
        ) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" must extend "%s".', $containerClass, DIContainer::class),
            );
        }

        $this->containerClass = $containerClass;

        return $this;
    }

    /**
     * Is auto wiring enabled.
     */
    public function doesUseAutowiring(): bool
    {
        return $this->useAutoWiring;
    }

    /**
     * Set auto wiring.
     *
     * @return static
     */
    public function setUseAutoWiring(bool $useAutoWiring): self
    {
        $this->useAutoWiring = $useAutoWiring;

        return $this;
    }

    /**
     * Are attributes enabled.
     */
    public function doesUseAttributes(): bool
    {
        return $this->useAttributes;
    }

    /**
     * @return static
     */
    public function setUseAttributes(bool $useAttributes): self
    {
        $this->useAttributes = $useAttributes;

        return $this;
    }

    /**
     * Is definition cache used.
     */
    public function doesUseDefinitionCache(): bool
    {
        return $this->useDefinitionCache;
    }

    /**
     * Set definition cache usage.
     *
     * @return static
     */
    public function setUseDefinitionCache(bool $useDefinitionCache): self
    {
        $this->useDefinitionCache = $useDefinitionCache;

        return $this;
    }

    /**
     * Get wrapping container.
     */
    public function getWrapContainer(): ?ContainerInterface
    {
        return $this->wrapContainer;
    }

    /**
     * Set wrapping container.
     *
     * @return static
     */
    public function setWrapContainer(ContainerInterface $wrapContainer): self
    {
        $this->wrapContainer = $wrapContainer;

        return $this;
    }

    /**
     * Get proxies path.
     */
    public function getProxiesPath(): ?string
    {
        return $this->proxiesPath;
    }

    /**
     * Set proxies path.
     *
     * @throws RuntimeException
     *
     * @return static
     */
    public function setProxiesPath(string $proxiesPath): self
    {
        if (!file_exists($proxiesPath) || !is_dir($proxiesPath) || !is_writable($proxiesPath)) {
            throw new RuntimeException(sprintf('Directory "%s" does not exist or is write protected.', $proxiesPath));
        }

        $this->proxiesPath = $proxiesPath;

        return $this;
    }

    /**
     * Get compilation path.
     */
    public function getCompilationPath(): ?string
    {
        return $this->compilationPath;
    }

    /**
     * Set compilation path.
     *
     * @throws RuntimeException
     *
     * @return static
     */
    public function setCompilationPath(string $compilationPath): self
    {
        if (!file_exists($compilationPath) || !is_dir($compilationPath) || !is_writable($compilationPath)) {
            throw new RuntimeException(sprintf(
                'Directory "%s" does not exist or is write protected.',
                $compilationPath,
            ));
        }

        $this->compilationPath = $compilationPath;

        return $this;
    }

    /**
     * Get compiled container class.
     *
     * @return class-string<DICompiledContainer>
     */
    public function getCompiledContainerClass(): string
    {
        return $this->compiledContainerClass;
    }

    /**
     * Set compiled container class.
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function setCompiledContainerClass(string $compiledContainerClass): self
    {
        if (
            !class_exists($compiledContainerClass)
            || (
                $compiledContainerClass !== DICompiledContainer::class
                && !is_subclass_of($compiledContainerClass, DICompiledContainer::class)
            )
        ) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" must extend "%s".', $compiledContainerClass, DICompiledContainer::class),
            );
        }

        $this->compiledContainerClass = $compiledContainerClass;

        return $this;
    }

    /**
     * @return list<string|array<string, scalar|object|Closure|null>>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param string|iterable<string|array<string, scalar|object|Closure|null>|mixed> $definitions
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function setDefinitions(string|iterable $definitions): self
    {
        if (\is_string($definitions)) {
            $definitions = [$definitions];
        }

        if ($definitions instanceof Traversable) {
            $definitions = iterator_to_array($definitions);
        }

        array_walk(
            $definitions,
            static function ($definition): void {
                if (!\is_array($definition) && !\is_string($definition)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'A definition must be an array or a file or directory path. "%s" given.',
                            \gettype($definition),
                        ),
                    );
                }
            },
        );

        /** @var list<string|array<string, scalar|object|Closure|null>> $definitions */
        $this->definitions = $definitions;

        return $this;
    }
}
