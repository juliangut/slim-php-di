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

namespace Jgut\Slim\PHPDI\Tests;

use Cache\Adapter\Doctrine\DoctrineCachePool;
use DI\Container as DIContainer;
use Doctrine\Common\Cache\VoidCache;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\Container;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * Configuration tests.
 */
class ConfigurationTest extends TestCase
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
        $configuration = new Configuration();

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
            'useAutoWiring' => false,
            'useAnnotations' => true,
            'ignorePhpDocErrors' => true,
            'definitionsCache' => new DoctrineCachePool(new VoidCache()),
            'definitions' => __DIR__ . '/files/definitions/valid/definitions.php',
            'proxiesPath' => sys_get_temp_dir(),
        ];

        $configuration = new Configuration($configs);

        self::assertEquals(DIContainer::class, $configuration->getContainerClass());
        self::assertFalse($configuration->doesUseAutowiring());
        self::assertTrue($configuration->doesUseAnnotations());
        self::assertTrue($configuration->doesIgnorePhpDocErrors());
        self::assertInstanceOf(CacheInterface::class, $configuration->getDefinitionsCache());
        self::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
        self::assertEquals(sys_get_temp_dir(), $configuration->getProxiesPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^class ".+" must extend DI\\Container/
     */
    public function testInvalidContainerClass()
    {
        new Configuration(['containerClass' => VoidCache::class]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Definitions must be a string or traversable. integer given
     */
    public function testInvalidDefinitionsType()
    {
        new Configuration(['definitions' => 10]);
    }

    public function testTraversableDefinitionType()
    {
        $configs = [
            'definitions' => new \ArrayIterator([__DIR__ . '/files/definitions/valid/definitions.php']),
        ];

        $configuration = new Configuration($configs);

        self::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
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
