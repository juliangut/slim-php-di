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
     * @param Configuration                                       $configuration
     * @param string|array|\DI\Definition\Source\DefinitionSource $definitions
     *
     * @return \DI\Container
     */
    public static function build(Configuration $configuration, $definitions = [])
    {
        $containerBuilder = self::getContainerBuilder($configuration);

        // Add default services definitions
        $containerBuilder->addDefinitions(require __DIR__ . '/definitions.php');

        // Add custom service definitions
        $containerBuilder->addDefinitions($definitions);

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
}
