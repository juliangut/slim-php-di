<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDITests;

use Jgut\Slim\PHPDI\ContainerBuilder;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::configureContainerBuilder
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::configureContainerCache
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::getDefinitions
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::getDefaultServicesDefinitions
     */
    public function testBuild()
    {
        $settings = [
            'php-di' => [
                'use_autowiring' => false,
                'use_annotations' => true,
                'ignore_phpdoc_errors' => true,
                'proxy_path' => 'fake/path',
            ],
        ];

        ContainerBuilder::build($settings);
    }

    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::getDefinitions
     */
    public function testBuildDefinitions()
    {
        $settings = [
            'php-di' => [
                'definitions' => [
                    'foo' => function () {
                        return 'bar';
                    },
                ],
            ],
        ];

        ContainerBuilder::build($settings);
    }

    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::configureContainerCache
     */
    public function testBuildCache()
    {
        $settings = [
            'php-di' => [
                'definitions_cache' => new \Doctrine\Common\Cache\VoidCache,
            ],
        ];

        ContainerBuilder::build($settings);
    }
}
