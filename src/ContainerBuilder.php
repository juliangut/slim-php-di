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

use DI\ContainerBuilder as DIContainerBuilder;

/**
 * Helper to create and configure a Container.
 *
 * Default Slim services are included in the generated container.
 */
class ContainerBuilder
{
    /**
     * Build PHP-DI container.
     *
     * @param Configuration|null $configuration
     *
     * @throws \RuntimeException
     *
     * @return \DI\Container
     */
    public static function build(Configuration $configuration = null)
    {
        if ($configuration === null) {
            $configuration = new Configuration();
        }

        $containerBuilder = self::getContainerBuilder($configuration);

        // Default definitions
        $containerBuilder->addDefinitions(require __DIR__ . '/definitions.php');

        // Custom definitions
        $containerBuilder->addDefinitions(self::parseDefinitions($configuration->getDefinitions()));

        return $containerBuilder->build();
    }

    /**
     * Get configured container builder.
     *
     * @param Configuration $configuration
     *
     * @return DIContainerBuilder
     */
    private static function getContainerBuilder(Configuration $configuration)
    {
        $containerBuilder = new DIContainerBuilder($configuration->getContainerClass());

        $containerBuilder->useAutowiring($configuration->doesUseAutowiring());
        $containerBuilder->useAnnotations($configuration->doesUseAnnotations());
        $containerBuilder->ignorePhpDocErrors($configuration->doesIgnorePhpDocErrors());

        if ($configuration->getDefinitionsCache()) {
            $containerBuilder->setDefinitionCache($configuration->getDefinitionsCache());
        }

        if ($configuration->getProxiesPath()) {
            $containerBuilder->writeProxiesToFile(true, $configuration->getProxiesPath());
        }

        return $containerBuilder;
    }

    /**
     * Parse definitions.
     *
     * @param array $definitions
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    private static function parseDefinitions(array $definitions)
    {
        if (!count($definitions)) {
            return $definitions;
        }

        $definitions = array_map(
            function ($definition) {
                if (is_array($definition)) {
                    return $definition;
                }

                return self::loadDefinitionsFromPath($definition);
            },
            $definitions
        );

        return call_user_func_array('array_merge', $definitions);
    }

    /**
     * Load definitions from path.
     *
     * @param string $path
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    private static function loadDefinitionsFromPath($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', $path));
        }

        if (is_file($path)) {
            return self::loadDefinitionsFromFile($path);
        }

        $definitions = [];
        foreach (glob($path . '/*.php', GLOB_ERR) as $file) {
            if (is_file($file)) {
                $definitions[] = self::loadDefinitionsFromFile($file);
            }
        }

        if (count($definitions) === 0) {
            throw new \RuntimeException(sprintf('No definition files loaded from "%s" path', $path));
        }

        return call_user_func_array('array_merge', $definitions);
    }

    /**
     * Load definitions from file.
     *
     * @param string $file
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    private static function loadDefinitionsFromFile($file)
    {
        if (!is_readable($file)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(sprintf('"%s" file is not readable', $file));
            // @codeCoverageIgnoreEnd
        }

        $definitions = require $file;

        if (!is_array($definitions)) {
            throw new \RuntimeException(
                sprintf('Definitions file should return an array. %s returned', gettype($definitions))
            );
        }

        return $definitions;
    }
}
