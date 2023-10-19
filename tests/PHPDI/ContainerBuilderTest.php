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

use DI\Container;
use DI\Definition\Source\SourceCache;
use Jgut\Slim\PHPDI\AbstractCompiledContainer;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * @internal
 */
class ContainerBuilderTest extends TestCase
{
    public function testNonExistingDefinitionsPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path "/fake/definitions/path" does not exist.');

        ContainerBuilder::build(new Configuration(['definitions' => '/fake/definitions/path']));
    }

    public function testInvalidDefinitionsFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Definitions file should return an array. ".+" returned\.$/');

        ContainerBuilder::build(new Configuration(['definitions' => __DIR__ . '/files/definitions/invalid']));
    }

    public function testDefault(): void
    {
        $container = ContainerBuilder::build();

        static::assertTrue($container->has(Configuration::class));
        static::assertTrue($container->has(ContainerInterface::class));
    }

    public function testCreation(): void
    {
        $containerStub = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'containerClass' => Container::class,
            'useAutoWiring' => true,
            'useDefinitionCache' => SourceCache::isSupported(),
            'wrapContainer' => $containerStub,
            'proxiesPath' => sys_get_temp_dir(),
            'compilationPath' => __DIR__ . '/files',
            'compiledContainerClass' => AbstractCompiledContainer::class,
            'definitions' => [
                __DIR__ . '/files/definitions/valid/definitions.php',
                __DIR__ . '/files/definitions/valid',
                [
                    'valid' => 'definition',
                ],
            ],
        ];

        $container = ContainerBuilder::build(new Configuration($configs));

        try {
            static::assertTrue($container->has('foo'));
            static::assertEquals('baz', $container->get('foo'));
            static::assertEquals('definition', $container->get('valid'));

            static::assertFileExists(__DIR__ . '/files/CompiledContainer.php');
        } finally {
            unlink(__DIR__ . '/files/CompiledContainer.php');
        }
    }
}
