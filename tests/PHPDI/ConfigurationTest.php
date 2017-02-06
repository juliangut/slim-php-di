<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Slim\PHPDI\Tests;

use DI\Container as DIContainer;
use Doctrine\Common\Cache\VoidCache;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\Container;

/**
 * Configuration tests.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * #@expectedExceptionMessage Configurations must be a traversable
     */
    public function testInvalidConfigurations()
    {
        new Configuration('');
    }

    public function testDefaults()
    {
        $configuration = new Configuration;

        self::assertEquals(Container::class, $configuration->getContainerClass());
        self::assertTrue($configuration->doesUseAutowiring());
        self::assertFalse($configuration->doesUseAnnotations());
        self::assertFalse($configuration->doesIgnorePhpDocErrors());
        self::assertNull($configuration->getDefinitionsCache());
        self::assertEquals([], $configuration->getDefinitions());
        self::assertNull($configuration->getProxiesPath());
    }

    public function testCreationConfigurations()
    {
        $configs = [
            'containerClass' => DIContainer::class,
            'useAutowiring' => false,
            'useAnnotations' => true,
            'ignorePhpDocErrors' => true,
            'definitionsCache' => new VoidCache(),
            'definitions' => __DIR__ . '/files/definitions/valid/definitions.php',
            'proxiesPath' => sys_get_temp_dir(),
        ];

        $configuration = new Configuration($configs);

        self::assertEquals(DIContainer::class, $configuration->getContainerClass());
        self::assertFalse($configuration->doesUseAutowiring());
        self::assertTrue($configuration->doesUseAnnotations());
        self::assertTrue($configuration->doesIgnorePhpDocErrors());
        self::assertInstanceOf(VoidCache::class, $configuration->getDefinitionsCache());
        self::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
        self::assertEquals(sys_get_temp_dir(), $configuration->getProxiesPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^class ".+" must implement all of this interfaces/
     */
    public function testInvalidContainerClass()
    {
        new Configuration(['containerClass' => VoidCache::class]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Definitions must be a string or an array. integer given
     */
    public function testInvalidDefinitionsType()
    {
        new Configuration(['definitions' => 10]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A definition must be an array or a file or directory path. integer given
     */
    public function testInvalidDefinitionType()
    {
        new Configuration(['definitions' => [10]]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage /fake/proxies/path directory does not exist
     */
    public function testInvalidProxyPath()
    {
        new Configuration(['proxiesPath' => '/fake/proxies/path']);
    }
}
