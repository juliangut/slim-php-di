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
use DI\ContainerBuilder as DIContainerBuilder;
use RuntimeException;

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
     * @throws RuntimeException
     */
    public static function build(?Configuration $configuration = null): DIContainer
    {
        if ($configuration === null) {
            $configuration = new Configuration();
        }

        $defaultDefinitions = array_merge(
            require __DIR__ . '/definitions.php',
            [Configuration::class => $configuration],
        );
        $customDefinitions = self::parseDefinitions($configuration->getDefinitions());

        return self::getContainerBuilder($configuration)
            ->addDefinitions($defaultDefinitions, ...$customDefinitions)
            ->build();
    }

    /**
     * Get configured container builder.
     */
    private static function getContainerBuilder(Configuration $configuration): DIContainerBuilder
    {
        $containerBuilder = new DIContainerBuilder($configuration->getContainerClass());

        $containerBuilder->useAutowiring($configuration->doesUseAutowiring());
        $containerBuilder->useAnnotations($configuration->doesUseAnnotations());
        $containerBuilder->ignorePhpDocErrors($configuration->doesIgnorePhpDocErrors());

        if ($configuration->doesUseDefinitionCache()) {
            $containerBuilder->enableDefinitionCache();
        }

        if ($configuration->getWrapContainer() !== null) {
            $containerBuilder->wrapContainer($configuration->getWrapContainer());
        }

        if ($configuration->getProxiesPath() !== null) {
            $containerBuilder->writeProxiesToFile(true, $configuration->getProxiesPath());
        }

        if ($configuration->getCompilationPath() !== null) {
            $containerBuilder->enableCompilation(
                $configuration->getCompilationPath(),
                'CompiledContainer',
                $configuration->getCompiledContainerClass(),
            );
        }

        return $containerBuilder;
    }

    /**
     * Parse definitions.
     *
     * @param array<string|array<string, mixed>> $definitions
     *
     * @throws RuntimeException
     *
     * @return array<string, mixed>
     */
    private static function parseDefinitions(array $definitions): array
    {
        if (\count($definitions) === 0) {
            return [];
        }

        return array_map(
            static function ($definition): array {
                if (\is_array($definition)) {
                    return $definition;
                }

                return self::loadDefinitionsFromPath($definition);
            },
            $definitions,
        );
    }

    /**
     * Load definitions from path.
     *
     * @throws RuntimeException
     *
     * @return array<string, mixed>
     */
    private static function loadDefinitionsFromPath(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('Path "%s" does not exist.', $path));
        }

        if (!is_dir($path)) {
            return self::loadDefinitionsFromFile($path);
        }

        $definitions = [];
        $files = glob($path . '/*.php', \GLOB_ERR);
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $definitions[] = self::loadDefinitionsFromFile($file);
                }
            }
        }

        return \count($definitions) === 0 ? [] : array_merge(...$definitions);
    }

    /**
     * Load definitions from file.
     *
     * @throws RuntimeException
     *
     * @return array<string, mixed>
     */
    private static function loadDefinitionsFromFile(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(sprintf('"%s" must be a readable file.', $file));
            // @codeCoverageIgnoreEnd
        }

        $definitions = require $file;

        if (!\is_array($definitions)) {
            throw new RuntimeException(
                sprintf('Definitions file should return an array. "%s" returned.', \gettype($definitions)),
            );
        }

        /** @var array<string, array<string, mixed>> $definitions */
        return $definitions;
    }
}
