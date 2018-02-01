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

use DI\CompiledContainer as DICompiledContainer;
use DI\Container as DIContainer;
use Jgut\Slim\PHPDI\AbstractCompiledContainer;
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
        self::assertNull($configuration->getProxiesPath());
        self::assertNull($configuration->getCompilationPath());
        self::assertEquals(AbstractCompiledContainer::class, $configuration->getCompiledContainerClass());
        self::assertEquals([], $configuration->getDefinitions());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following configuration parameters are not recognized: unknown
     */
    public function testUnknownParameter()
    {
        new Configuration(['unknown' => 'unknown']);
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
            'useDefinitionCache' => true,
            'ignorePhpDocErrors' => true,
            'wrapContainer' => $containerStub,
            'proxiesPath' => sys_get_temp_dir(),
            'compilationPath' => __DIR__,
            'compiledContainerClass' => DICompiledContainer::class,
            'definitions' => __DIR__ . '/files/definitions/valid/definitions.php',
        ];

        $configuration = new Configuration($configs);

        self::assertEquals(DIContainer::class, $configuration->getContainerClass());
        self::assertFalse($configuration->doesUseAutowiring());
        self::assertTrue($configuration->doesUseAnnotations());
        self::assertTrue($configuration->doesUseDefinitionCache());
        self::assertTrue($configuration->doesIgnorePhpDocErrors());
        self::assertEquals($containerStub, $configuration->getWrapContainer());
        self::assertEquals(sys_get_temp_dir(), $configuration->getProxiesPath());
        self::assertEquals(__DIR__, $configuration->getCompilationPath());
        self::assertEquals(DICompiledContainer::class, $configuration->getCompiledContainerClass());
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

    public function testTraversableDefinitionType()
    {
        $configs = [
            'definitions' => new \ArrayIterator([__DIR__ . '/files/definitions/valid/definitions.php']),
        ];

        $configuration = new Configuration($configs);

        self::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage /fake/proxies/path/ directory does not exist or is write protected
     */
    public function testInvalidProxyPath()
    {
        new Configuration(['proxiesPath' => '/fake/proxies/path/']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage /fake/compilation/path/ directory does not exist or is write protected
     */
    public function testInvalidCompilationPath()
    {
        new Configuration(['compilationPath' => '/fake/compilation/path/']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^class ".+" must extend DI\\CompiledContainer/
     */
    public function testInvalidCompiledContainerClass()
    {
        new Configuration(['compiledContainerClass' => 'NonExistingClass']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A definition must be an array or a file or directory path. integer given
     */
    public function testInvalidArrayDefinitionType()
    {
        new Configuration(['definitions' => [10]]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Definitions must be a string or traversable. integer given
     */
    public function testInvalidDefinitionType()
    {
        new Configuration(['definitions' => 10]);
    }
}
