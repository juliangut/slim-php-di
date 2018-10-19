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
use Jgut\Slim\PHPDI\AbstractCompiledContainer;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Container builder tests.
 */
class ContainerBuilderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Path "/fake/definitions/path" does not exist
     */
    public function testNonExistingDefinitionsPath()
    {
        ContainerBuilder::build(new Configuration(['definitions' => '/fake/definitions/path']));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Definitions file should return an array. ".+" returned$/
     */
    public function testInvalidDefinitionsFile()
    {
        ContainerBuilder::build(new Configuration(['definitions' => __DIR__ . '/files/definitions/invalid']));
    }

    public function testCreation()
    {
        /** @var ContainerInterface $containerStub */
        $containerStub = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration = [
            'containerClass' => Container::class,
            'useAutoWiring' => true,
            'useAnnotations' => true,
            'useDefinitionCache' => true,
            'ignorePhpDocErrors' => true,
            'wrapContainer' => $containerStub,
            'proxiesPath' => \sys_get_temp_dir(),
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

        if (\ini_get('apc.enabled') === '0') {
            unset($configuration['useDefinitionCache']);
        }

        $container = ContainerBuilder::build(new Configuration($configuration));

        self::assertInstanceOf(Container::class, $container);
        self::assertTrue($container->has('foo'));
        self::assertEquals('baz', $container->get('foo'));
        self::assertFileExists(__DIR__ . '/files/CompiledContainer.php');

        \unlink(__DIR__ . '/files/CompiledContainer.php');
    }
}
