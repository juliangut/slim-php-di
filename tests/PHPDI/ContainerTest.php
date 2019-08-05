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

use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Container tests.
 */
class ContainerTest extends TestCase
{
    /**
     * @var \Jgut\Slim\PHPDI\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->container = ContainerBuilder::build(new Configuration());
    }

    /**
     * @expectedException \Psr\Container\NotFoundExceptionInterface
     * @expectedExceptionMessage No entry or class found for "baz"
     */
    public function testGetNonExistent()
    {
        self::assertFalse($this->container->has('baz'));
        $this->container['baz'];
    }

    /**
     * @expectedException \Psr\Container\NotFoundExceptionInterface
     * @expectedExceptionMessage No entry or class found for "settings.baz"
     */
    public function testGetNonExistentWithDots()
    {
        self::assertFalse($this->container->has('settings.baz'));
        $this->container['settings.baz'];
    }

    /**
     * @expectedException \Psr\Container\NotFoundExceptionInterface
     * @expectedExceptionMessage No entry or class found for "settings.foo.bar.baz"
     */
    public function testGetShadowed()
    {
        $settings = [
            'foo' => [
                'bar' => [
                    'baz' => 'shadowed!',
                ],
            ],
            'foo.bar' => 'bang!',
        ];
        $this->container->set('settings', $settings);

        self::assertTrue($this->container->has('settings.foo.bar'));
        self::assertEquals('bang!', $this->container->get('settings.foo.bar'));

        self::assertFalse($this->container->has('settings.foo.bar.baz'));
        $this->container->get('settings.foo.bar.baz');
    }

    public function testSettingsAccess()
    {
        $settings = [
            'foo' => [
                'bar' => [
                    'baz' => 'found!',
                    'bam' => [],
                ],
            ],
        ];
        $this->container->set('settings', $settings);

        self::assertTrue($this->container->has('settings.foo'));
        self::assertEquals(['bar' => ['baz' => 'found!', 'bam' => []]], $this->container->get('settings.foo'));

        self::assertTrue($this->container->has('settings.foo.bar'));
        self::assertEquals(['baz' => 'found!', 'bam' => []], $this->container->get('settings.foo.bar'));

        self::assertTrue($this->container->has('settings.foo.bar.baz'));
        self::assertEquals('found!', $this->container->get('settings.foo.bar.baz'));

        self::assertTrue($this->container->has('settings.foo.bar.bam'));
        self::assertEquals([], $this->container->get('settings.foo.bar.bam'));
    }

    /**
     * @expectedException \Psr\Container\ContainerExceptionInterface
     * @expectedExceptionMessage Entry "foo" cannot be resolved: the class doesn't exist
     */
    public function testUnresolvable()
    {
        $configuration = new Configuration([
            'definitions' => [
                ['foo' => \DI\create('\\Unknown\\Foo\\Bar')],
            ],
        ]);

        $container = ContainerBuilder::build($configuration);

        $container->get('foo');
    }

    public function testSetterGetter()
    {
        $this->container['foo'] = 'bar';
        self::assertTrue($this->container->has('foo'));
        self::assertEquals('bar', $this->container->get('foo'));

        $this->container['bar'] = 'baz';
        self::assertTrue(isset($this->container['bar']));
        self::assertEquals('baz', $this->container['bar']);

        $this->container['baz'] = 'bam';
        self::assertTrue(isset($this->container->baz));
        self::assertEquals('bam', $this->container->baz);

        $this->container->bam = 'foo';
        self::assertTrue($this->container->has('bam'));
        self::assertEquals('foo', $this->container->bam);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage It is not possible to unset container definitions
     */
    public function testUnset()
    {
        unset($this->container->foo);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage It is not possible to unset container definitions
     */
    public function testUnsetArray()
    {
        unset($this->container['foo']);
    }

    public function testDefaultServices()
    {
        self::assertTrue($this->container->has(Configuration::class));
        self::assertInstanceOf(Configuration::class, $this->container->get(Configuration::class));

        self::assertTrue($this->container->has(ContainerInterface::class));
        self::assertEquals($this->container, $this->container->get(ContainerInterface::class));
    }
}
