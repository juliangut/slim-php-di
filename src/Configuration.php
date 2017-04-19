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
use Psr\SimpleCache\CacheInterface;

/**
 * Container builder configuration.
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
    protected $ignorePhpDocErrors = false;

    /**
     * @var CacheInterface
     */
    protected $definitionsCache;

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @var string
     */
    protected $proxiesPath;

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!is_array($configurations) && !$configurations instanceof \Traversable) {
            throw new \InvalidArgumentException('Configurations must be a traversable');
        }

        $this->seedConfigurations($configurations);
    }

    /**
     * Seed configurations.
     *
     * @param array|\Traversable $configurations
     */
    protected function seedConfigurations($configurations)
    {
        $configs = [
            'containerClass',
            'useAutoWiring',
            'useAnnotations',
            'ignorePhpDocErrors',
            'definitionsCache',
            'definitions',
            'proxiesPath',
        ];

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                $callback = [$this, 'set' . ucfirst($config)];

                call_user_func($callback, $configurations[$config]);
            }
        }
    }

    /**
     * get container class.
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
     * @return $this
     */
    public function setContainerClass(string $containerClass)
    {
        if (!class_exists($containerClass)
            || ($containerClass !== DIContainer::class
                && !is_subclass_of($containerClass, DIContainer::class)
            )
        ) {
            throw new \InvalidArgumentException(
                sprintf('class "%s" must extend %s', $containerClass, DIContainer::class)
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
     * @return $this
     */
    public function setUseAutoWiring($useAutoWiring)
    {
        $this->useAutoWiring = $useAutoWiring === true;

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
     * @return $this
     */
    public function setUseAnnotations(bool $useAnnotations)
    {
        $this->useAnnotations = $useAnnotations;

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
     * @return $this
     */
    public function setIgnorePhpDocErrors($ignorePhpDocErrors)
    {
        $this->ignorePhpDocErrors = $ignorePhpDocErrors === true;

        return $this;
    }

    /**
     * Get definitions cache.
     *
     * @return CacheInterface
     */
    public function getDefinitionsCache()
    {
        return $this->definitionsCache;
    }

    /**
     * Set definitions cache.
     *
     * @param CacheInterface $definitionsCache
     *
     * @return $this
     */
    public function setDefinitionsCache(CacheInterface $definitionsCache)
    {
        $this->definitionsCache = $definitionsCache;

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
     * @return $this
     */
    public function setDefinitions($definitions)
    {
        if (is_string($definitions)) {
            $definitions = [$definitions];
        }

        if ($definitions instanceof \Traversable) {
            $definitions = iterator_to_array($definitions);
        }

        if (!is_array($definitions)) {
            throw new \InvalidArgumentException(
                sprintf('Definitions must be a string or traversable. %s given', gettype($definitions))
            );
        }

        array_walk(
            $definitions,
            function ($definition) {
                if (!is_array($definition) && !is_string($definition)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'A definition must be an array or a file or directory path. %s given',
                            gettype($definition)
                        )
                    );
                }
            }
        );

        $this->definitions = $definitions;

        return $this;
    }

    /**
     * Get proxies path.
     *
     * @return string
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
     * @return $this
     */
    public function setProxiesPath(string $proxiesPath)
    {
        if (!file_exists($proxiesPath) || !is_dir($proxiesPath)) {
            throw new \RuntimeException(sprintf('%s directory does not exist', $proxiesPath));
        }

        $this->proxiesPath = rtrim($proxiesPath, DIRECTORY_SEPARATOR);

        return $this;
    }
}
