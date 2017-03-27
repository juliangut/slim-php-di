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

use DI\Container;
use Doctrine\Common\Cache\VoidCache;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;

/**
 * Container builder tests.
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionMessageRegExp /^No definition files loaded from ".+" path$/
     */
    public function testInvalidDefinitionsPath()
    {
        ContainerBuilder::build(new Configuration(['definitions' => __DIR__ . '/files']));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Definitions file should return an array. .+ returned$/
     */
    public function testInvalidDefinitionsFile()
    {
        ContainerBuilder::build(new Configuration(['definitions' => __DIR__ . '/files/definitions/invalid']));
    }

    public function testCreation()
    {
        $configuration = new Configuration([
            'containerClass' => Container::class,
            'useAutowiring' => true,
            'useAnnotations' => true,
            'ignorePhpDocErrors' => true,
            'definitionsCache' => new VoidCache(),
            'definitions' => [
                __DIR__ . '/files/definitions/valid/definitions.php',
                __DIR__ . '/files/definitions/valid',
                [
                    'valid' => 'definition',
                ]
            ],
            'proxiesPath' => sys_get_temp_dir(),
        ]);

        $container = ContainerBuilder::build($configuration);

        self::assertInstanceOf(Container::class, $container);
        self::assertTrue($container->has('foo'));
        self::assertEquals('baz', $container->get('foo'));
    }
}
