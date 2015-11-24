<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 *
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI\Tests;

use Jgut\Slim\PHPDI\ContainerBuilder;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::configureContainerBuilder
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::configureContainerProxies
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::getDefaultServicesDefinitions
     */
    public function testBuild()
    {
        $settings = [
            'settings' => [
                'php-di' => [
                    'use_autowiring' => false,
                    'use_annotations' => true,
                    'ignore_phpdoc_errors' => true,
                    'proxy_path' => 'fake/path',
                ],
            ],
        ];

        $this->assertInstanceOf('\Jgut\Slim\PHPDI\Container', ContainerBuilder::build($settings));
    }

    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::configureContainerCache
     */
    public function testBuildCache()
    {
        $settings = [
            'settings' => [
                'php-di' => [
                    'definitions_cache' => new \Doctrine\Common\Cache\VoidCache,
                ],
            ],
        ];

        $this->assertInstanceOf('\Jgut\Slim\PHPDI\Container', ContainerBuilder::build($settings));
    }

    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     */
    public function testBuildDefinitions()
    {
        $settings = [
            'foo' => 'bar',
        ];

        $container = ContainerBuilder::build($settings);

        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     */
    public function testBuildDefinitionsOverride()
    {
        $definitions = [
            'settings' => 'foo',
            'foo' => 'baz',
        ];

        $container = ContainerBuilder::build([], $definitions);

        $this->assertEquals('foo', $container->get('settings'));
        $this->assertEquals('baz', $container->get('foo'));
    }
}
