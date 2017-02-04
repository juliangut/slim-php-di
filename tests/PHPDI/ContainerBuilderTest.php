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
    public function testBuildConfigurations()
    {
        $configuration = new Configuration([
            'containerClass' => Container::class,
            'useAutowiring' => true,
            'useAnnotations' => true,
            'ignorePhpDocErrors' => true,
            'definitionsCache' => new VoidCache,
            'proxiesPath' => sys_get_temp_dir(),
        ]);

        self::assertInstanceOf(Container::class, ContainerBuilder::build($configuration));
    }

    public function testCustomDefinitions()
    {
        $definitions = [
            'foo' => 'bar',
        ];

        $container = ContainerBuilder::build(new Configuration, $definitions);

        self::assertEquals('bar', $container->get('foo'));
    }
}
