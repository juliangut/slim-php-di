<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Tests;

use ArrayIterator;
use DI\CompiledContainer as DICompiledContainer;
use DI\Container as DIContainer;
use InvalidArgumentException;
use Jgut\Slim\PHPDI\AbstractCompiledContainer;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use TypeError;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @internal
 */
class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $configuration = new Configuration();

        static::assertEquals(Container::class, $configuration->getContainerClass());
        static::assertTrue($configuration->doesUseAutowiring());
        static::assertNull($configuration->getProxiesPath());
        static::assertNull($configuration->getCompilationPath());
        static::assertEquals(AbstractCompiledContainer::class, $configuration->getCompiledContainerClass());
        static::assertEquals([], $configuration->getDefinitions());
    }

    public function testUnknownParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following configuration parameters are not recognized: unknown.');

        new Configuration(['unknown' => 'unknown']);
    }

    public function testCreationConfigurations(): void
    {
        $containerStub = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'containerClass' => DIContainer::class,
            'useAutoWiring' => false,
            'useAttributes' => true,
            'useDefinitionCache' => true,
            'wrapContainer' => $containerStub,
            'proxiesPath' => sys_get_temp_dir(),
            'compilationPath' => __DIR__,
            'compiledContainerClass' => DICompiledContainer::class,
            'definitions' => __DIR__ . '/files/definitions/valid/definitions.php',
        ];

        $configuration = new Configuration(new ArrayIterator($configs));

        static::assertEquals(DIContainer::class, $configuration->getContainerClass());
        static::assertFalse($configuration->doesUseAutowiring());
        static::assertTrue($configuration->doesUseAttributes());
        static::assertTrue($configuration->doesUseDefinitionCache());
        static::assertEquals($containerStub, $configuration->getWrapContainer());
        static::assertEquals(sys_get_temp_dir(), $configuration->getProxiesPath());
        static::assertEquals(__DIR__, $configuration->getCompilationPath());
        static::assertEquals(DICompiledContainer::class, $configuration->getCompiledContainerClass());
        static::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
    }

    public function testInvalidContainerClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Class ".+" must extend "DI\\\Container"\.$/');

        new Configuration(['containerClass' => 'NonExistingClass']);
    }

    public function testTraversableDefinitionType(): void
    {
        $configs = [
            'definitions' => new ArrayIterator([__DIR__ . '/files/definitions/valid/definitions.php']),
        ];

        $configuration = new Configuration($configs);

        static::assertEquals([__DIR__ . '/files/definitions/valid/definitions.php'], $configuration->getDefinitions());
    }

    public function testInvalidProxyPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Directory "/fake/proxies/path/" does not exist or is write protected.');

        new Configuration(['proxiesPath' => '/fake/proxies/path/']);
    }

    public function testInvalidCompilationPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Directory "/fake/compilation/path/" does not exist or is write protected.');

        new Configuration(['compilationPath' => '/fake/compilation/path/']);
    }

    public function testInvalidCompiledContainerClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Class ".+" must extend "DI\\\CompiledContainer"\.$/');

        new Configuration(['compiledContainerClass' => 'NonExistingClass']);
    }

    public function testInvalidArrayDefinitionType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A definition must be an array or a file or directory path. "integer" given.');

        new Configuration(['definitions' => [10]]);
    }

    public function testInvalidDefinitionType(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches(
            '/^.+setDefinitions\(\): Argument #1 \(\$definitions\) must be of type .+, int given/',
        );

        new Configuration(['definitions' => 10]);
    }
}
