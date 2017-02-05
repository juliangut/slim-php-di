<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Slim\PHPDI;

use Doctrine\Common\Cache\Cache;

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
    protected $useAutowiring = true;

    /**
     * @var bool
     */
    protected $useAnnotations = false;

    /**
     * @var bool
     */
    protected $ignorePhpDocErrors = false;

    /**
     * @var Cache
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

        if (count($configurations)) {
            $this->seedConfigurations($configurations);
        }
    }

    /**
     * Seed configurations.
     *
     * @param $configurations
     */
    protected function seedConfigurations($configurations)
    {
        $configs = [
            'containerClass',
            'useAutowiring',
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
    public function getContainerClass()
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
    public function setContainerClass($containerClass)
    {
        $interfaces = [
            'Interop\Container\ContainerInterface',
            'DI\FactoryInterface',
            'DI\InvokerInterface',
        ];

        if (count(array_intersect($interfaces, class_implements($containerClass))) === 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'class "%s" must implement all of this interfaces: %s',
                    $containerClass,
                    implode(', ', $interfaces)
                )
            );
        }

        $this->containerClass = $containerClass;

        return $this;
    }

    /**
     * Is autowiring enabled.
     *
     * @return bool
     */
    public function doesUseAutowiring()
    {
        return $this->useAutowiring;
    }

    /**
     * Set autowiring.
     *
     * @param bool $useAutowiring
     *
     * @return static
     */
    public function setUseAutowiring($useAutowiring)
    {
        $this->useAutowiring = $useAutowiring === true;

        return $this;
    }

    /**
     * Are annotations enabled.
     *
     * @return bool
     */
    public function doesUseAnnotations()
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
    public function setUseAnnotations($useAnnotations)
    {
        $this->useAnnotations = $useAnnotations === true;

        return $this;
    }

    /**
     * Are PhpDoc errors ignored.
     *
     * @return bool
     */
    public function doesIgnorePhpDocErrors()
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
    public function setIgnorePhpDocErrors($ignorePhpDocErrors)
    {
        $this->ignorePhpDocErrors = $ignorePhpDocErrors === true;

        return $this;
    }

    /**
     * Get definitions cache.
     *
     * @return Cache
     */
    public function getDefinitionsCache()
    {
        return $this->definitionsCache;
    }

    /**
     * Set definitions cache.
     *
     * @param Cache $definitionsCache
     *
     * @return static
     */
    public function setDefinitionsCache(Cache $definitionsCache)
    {
        $this->definitionsCache = $definitionsCache;

        return $this;
    }

    /**
     * Get definitions.
     *
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Set definitions.
     *
     * @param string|array $definitions
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setDefinitions($definitions)
    {
        if (is_string($definitions)) {
            $definitions = [$definitions];
        }

        if (!is_array($definitions)) {
            throw new \InvalidArgumentException(
                sprintf('Definitions must be a string or an array. %s given', gettype($definitions))
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
     * @return static
     */
    public function setProxiesPath($proxiesPath)
    {
        if (!file_exists($proxiesPath) || !is_dir($proxiesPath)) {
            throw new \RuntimeException(sprintf('%s directory does not exist', $proxiesPath));
        }

        $this->proxiesPath = rtrim($proxiesPath, DIRECTORY_SEPARATOR);

        return $this;
    }
}
