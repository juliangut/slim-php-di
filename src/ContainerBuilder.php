<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI;

use DI\ContainerBuilder as DIContainerBuilder;

class ContainerBuilder
{
    /**
     * Build PHP-DI container for Slim Framework.
     *
     * @param array $userSettings user settings for Slim
     * @param array $definitions PHP definitions for PHP-DI
     *
     * @return \Jgut\Slim\Container
     */
    public static function build(array $userSettings = [], array $definitions = [])
    {
        $containerBuilder = new DIContainerBuilder('\Jgut\Slim\PHPDI\Container');

        if (isset($userSettings['php-di'])) {
            $settings = $userSettings['php-di'];

            if (isset($settings['use_autowiring'])) {
                $containerBuilder->useAutowiring((bool) $settings['use_autowiring']);
            }

            if (isset($settings['use_annotations'])) {
                $containerBuilder->useAnnotations((bool) $settings['use_annotations']);
            }

            if (isset($settings['ignore_phpdoc_errors'])) {
                $containerBuilder->ignorePhpDocErrors((bool) $settings['ignore_phpdoc_errors']);
            }

            // setDefinitionCache missing

            if (isset($settings['proxy_path']) && !empty($settings['proxy_path'])) {
                $containerBuilder->writeProxiesToFile(true, $settings['proxy_path']);
            }

            if (isset($settings['definitions']) && is_array($settings['definitions'])) {
                $definitions = array_merge($settings['definitions'], $definitions);
            }
        }

        $containerBuilder->addDefinitions($definitions);

        $container = $containerBuilder->build();
        $container->registerDefaultServices($userSettings);

        return $container;
    }
}
