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
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConfigurationTest extends TestCase
{
    public function testInvalidConfigurations(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configurations must be a traversable.');

        new Configuration('');
    }

    public function testDefaults(): void
    {
        $configuration = new Configuration();

        static::assertEquals(Container::class, $configuration->getContainerClass());
        static::assertTrue($configuration->doesUseAutowiring());
        static::assertFalse($configuration->doesUseAnnotations());
        static::assertFalse($configuration->doesIgnorePhpDocErrors());
        static::assertNull($configuration->getProxiesPath());
        static::assertNull($configuration->getCompilationPath());
        static::assertEquals(AbstractCompiledContainer::class, $configuration->getCompiledContainerClass());
        static::assertEquals([], $configuration->getDefinitions());
    }

    public function testUnknownParameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The following configuration parameters are not recognized: unknown.');

        new Configuration(['unknown' => 'unknown']);
    }

    public function testCreationConfigurations(): void
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
            'proxiesPath' => \sys_get_temp_dir(),
            'compilationPath' => __DIR__,
            'compiledContainerClass' => DICompiledContainer::class,
            'definitions' => __DIR__ . '/files/definitions/valid/definitions.php',
        ];

        $configuration = new Configuration(new \ArrayIterator($configs));

        static::assertEquals(DIContainer::class, $configuration->getContainerClass());
        static::assertFalse($configuration->doesUseAutowiring());
        static::assertTrue($configuration->doesUseAnnotations());
        static::assertTrue($configuration->doesUseDefinitionCache());
        static::assertTrue($configuration->doesIgnorePhpDocErrors());
        static::assertEquals($containerStub, $configuration->getWrapContainer());
        static::assertEquals(\sys_get_temp_dir(), $configuration->getProxiesPath());
        static::assertEquals(__DIR__, $configuration->getCompilationPath());
        static::assertEquals(DICompiledContainer::class, $configuration->getCompiledContainerClass());
        static::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
    }

    public function testInvalidContainerClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^class ".+" must extend "DI\\\Container"\.$/');

        new Configuration(['containerClass' => 'NonExistingClass']);
    }

    public function testTraversableDefinitionType(): void
    {
        $configs = [
            'definitions' => new \ArrayIterator([__DIR__ . '/files/definitions/valid/definitions.php']),
        ];

        $configuration = new Configuration($configs);

        static::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
    }

    public function testInvalidProxyPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('/fake/proxies/path/ directory does not exist or is write protected.');

        new Configuration(['proxiesPath' => '/fake/proxies/path/']);
    }

    public function testInvalidCompilationPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('/fake/compilation/path/ directory does not exist or is write protected.');

        new Configuration(['compilationPath' => '/fake/compilation/path/']);
    }

    public function testInvalidCompiledContainerClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^class ".+" must extend "DI\\\CompiledContainer"\.$/');

        new Configuration(['compiledContainerClass' => 'NonExistingClass']);
    }

    public function testInvalidArrayDefinitionType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A definition must be an array or a file or directory path. "integer" given.');

        new Configuration(['definitions' => [10]]);
    }

    public function testInvalidDefinitionType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Definitions must be a string or traversable. "integer" given.');

        new Configuration(['definitions' => 10]);
    }
}
