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

use DI\CompiledContainer as DICompiledContainer;
use DI\Container as DIContainer;
use Psr\Container\ContainerInterface;

/**
 * Container builder configuration.
 *
 * @SuppressWarnings(PMD.LongVariable)
 */
class Configuration
{
    /**
     * @var string
     */
    protected $containerClass = Container::class;

    /**
     * @var bool
     */
    protected $useAutoWiring = true;

    /**
     * @var bool
     */
    protected $useAnnotations = false;

    /**
     * @var bool
     */
    protected $useDefinitionCache = false;

    /**
     * @var bool
     */
    protected $ignorePhpDocErrors = false;

    /**
     * @var ContainerInterface
     */
    protected $wrapContainer;

    /**
     * @var string
     */
    protected $proxiesPath;

    /**
     * @var string
     */
    protected $compilationPath;

    /**
     * @var string
     */
    protected $compiledContainerClass = AbstractCompiledContainer::class;

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!\is_array($configurations) && !$configurations instanceof \Traversable) {
            throw new \InvalidArgumentException('Configurations must be a traversable');
        }

        $configs = \array_keys(\get_object_vars($this));

        $unknownParameters = \array_diff(\array_keys($configurations), $configs);
        if (\count($unknownParameters) > 0) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'The following configuration parameters are not recognized: %s',
                    \implode(', ', $unknownParameters)
                )
            );
        }

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                $callback = [$this, 'set' . \ucfirst($config)];

                \call_user_func($callback, $configurations[$config]);
            }
        }
    }

    /**
     * Get container class.
     *
     * @return string
     */
    public function getContainerClass(): string
    {
        return $this->containerClass;
    }

    /**
     * Set container class.
     *
     * @param string $containerClass
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setContainerClass(string $containerClass)
    {
        if (!\class_exists($containerClass)
            || (
                $containerClass !== DIContainer::class
                && !\is_subclass_of($containerClass, DIContainer::class)
            )
        ) {
            throw new \InvalidArgumentException(
                \sprintf('class "%s" must extend %s', $containerClass, DIContainer::class)
            );
        }

        $this->containerClass = $containerClass;

        return $this;
    }

    /**
     * Is auto wiring enabled.
     *
     * @return bool
     */
    public function doesUseAutowiring(): bool
    {
        return $this->useAutoWiring;
    }

    /**
     * Set auto wiring.
     *
     * @param bool $useAutoWiring
     *
     * @return static
     */
    public function setUseAutoWiring(bool $useAutoWiring)
    {
        $this->useAutoWiring = $useAutoWiring;

        return $this;
    }

    /**
     * Are annotations enabled.
     *
     * @return bool
     */
    public function doesUseAnnotations(): bool
    {
        return $this->useAnnotations;
    }

    /**
     * Set annotations.
     *
     * @param bool $useAnnotations
     *
     * @return static
     */
    public function setUseAnnotations(bool $useAnnotations)
    {
        $this->useAnnotations = $useAnnotations;

        return $this;
    }

    /**
     * Is definition cache used.
     *
     * @return bool
     */
    public function doesUseDefinitionCache(): bool
    {
        return $this->useDefinitionCache;
    }

    /**
     * Set definition cache usage.
     *
     * @param bool $useDefinitionCache
     *
     * @return $this
     */
    public function setUseDefinitionCache(bool $useDefinitionCache)
    {
        $this->useDefinitionCache = $useDefinitionCache;

        return $this;
    }

    /**
     * Are PhpDoc errors ignored.
     *
     * @return bool
     */
    public function doesIgnorePhpDocErrors(): bool
    {
        return $this->ignorePhpDocErrors;
    }

    /**
     * Set ignoring PhpDoc errors.
     *
     * @param bool $ignorePhpDocErrors
     *
     * @return static
     */
    public function setIgnorePhpDocErrors(bool $ignorePhpDocErrors)
    {
        $this->ignorePhpDocErrors = $ignorePhpDocErrors;

        return $this;
    }

    /**
     * Get wrapping container.
     *
     * @return ContainerInterface|null
     */
    public function getWrapContainer()
    {
        return $this->wrapContainer;
    }

    /**
     * Set wrapping container.
     *
     * @param ContainerInterface $wrapContainer
     *
     * @return static
     */
    public function setWrapContainer(ContainerInterface $wrapContainer)
    {
        $this->wrapContainer = $wrapContainer;

        return $this;
    }

    /**
     * Get proxies path.
     *
     * @return string|null
     */
    public function getProxiesPath()
    {
        return $this->proxiesPath;
    }

    /**
     * Set proxies path.
     *
     * @param string $proxiesPath
     *
     * @throws \RuntimeException
     *
     * @return static
     */
    public function setProxiesPath(string $proxiesPath)
    {
        if (!\file_exists($proxiesPath) || !\is_dir($proxiesPath) || !\is_writable($proxiesPath)) {
            throw new \RuntimeException(\sprintf('%s directory does not exist or is write protected', $proxiesPath));
        }

        $this->proxiesPath = $proxiesPath;

        return $this;
    }

    /**
     * Get compilation path.
     *
     * @return string
     */
    public function getCompilationPath()
    {
        return $this->compilationPath;
    }

    /**
     * Set compilation path.
     *
     * @param string $compilationPath
     *
     * @throws \RuntimeException
     *
     * @return static
     */
    public function setCompilationPath(string $compilationPath)
    {
        if (!\file_exists($compilationPath) || !\is_dir($compilationPath) || !\is_writable($compilationPath)) {
            throw new \RuntimeException(\sprintf(
                '%s directory does not exist or is write protected',
                $compilationPath
            ));
        }

        $this->compilationPath = $compilationPath;

        return $this;
    }

    /**
     * Get compiled container class.
     *
     * @return string
     */
    public function getCompiledContainerClass(): string
    {
        return $this->compiledContainerClass;
    }

    /**
     * Set compiled container class.
     *
     * @param string $compiledContainerClass
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setCompiledContainerClass(string $compiledContainerClass)
    {
        if (!\class_exists($compiledContainerClass)
            || (
                $compiledContainerClass !== DICompiledContainer::class
                && !\is_subclass_of($compiledContainerClass, DICompiledContainer::class)
            )
        ) {
            throw new \InvalidArgumentException(
                \sprintf('class "%s" must extend %s', $compiledContainerClass, DICompiledContainer::class)
            );
        }

        $this->compiledContainerClass = $compiledContainerClass;

        return $this;
    }

    /**
     * Get definitions.
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Set definitions.
     *
     * @param string|array|\Traversable $definitions
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setDefinitions($definitions)
    {
        if (\is_string($definitions)) {
            $definitions = [$definitions];
        }

        if ($definitions instanceof \Traversable) {
            $definitions = \iterator_to_array($definitions);
        }

        if (!\is_array($definitions)) {
            throw new \InvalidArgumentException(
                \sprintf('Definitions must be a string or traversable. %s given', \gettype($definitions))
            );
        }

        \array_walk(
            $definitions,
            function ($definition) {
                if (!\is_array($definition) && !\is_string($definition)) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            'A definition must be an array or a file or directory path. %s given',
                            \gettype($definition)
                        )
                    );
                }
            }
        );

        $this->definitions = $definitions;

        return $this;
    }
}
