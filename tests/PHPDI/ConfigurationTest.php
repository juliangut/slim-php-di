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

use DI\Container as DIContainer;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

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
        self::assertEquals([], $configuration->getDefinitions());
        self::assertNull($configuration->getProxiesPath());
        self::assertNull($configuration->getCompilationPath());
    }

    public function testCreationConfigurations()
    {
        /** @var ContainerInterface $containerStub */
        $containerStub = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'containerClass' => DIContainer::class,
            'useAutoWiring' => false,
            'useAnnotations' => true,
            'ignorePhpDocErrors' => true,
            'wrapContainer' => $containerStub,
            'proxiesPath' => sys_get_temp_dir(),
            'compilationPath' => __DIR__,
            'definitions' => __DIR__ . '/files/definitions/valid/definitions.php',
        ];

        $configuration = new Configuration($configs);

        self::assertEquals(DIContainer::class, $configuration->getContainerClass());
        self::assertFalse($configuration->doesUseAutowiring());
        self::assertTrue($configuration->doesUseAnnotations());
        self::assertTrue($configuration->doesIgnorePhpDocErrors());
        self::assertEquals($containerStub, $configuration->getWrapContainer());
        self::assertEquals(sys_get_temp_dir(), $configuration->getProxiesPath());
        self::assertEquals(__DIR__, $configuration->getCompilationPath());
        self::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^class ".+" must extend DI\\Container/
     */
    public function testInvalidContainerClass()
    {
        new Configuration(['containerClass' => 'NonExistingClass']);
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
