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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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
    public function setUp(): void
    {
        $this->container = ContainerBuilder::build();
    }

    public function testGetNonExistent(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('No entry or class found for "baz"');

        static::assertFalse($this->container->has('baz'));
        $this->container['baz'];
    }

    public function testGetNonExistentWithDots(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('No entry or class found for "settings.baz"');

        static::assertFalse($this->container->has('settings.baz'));
        $this->container['settings.baz'];
    }

    public function testGetShadowed(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('No entry or class found for "settings.foo.bar.baz"');

        $settings = [
            'foo' => [
                'bar' => [
                    'baz' => 'shadowed!',
                ],
            ],
            'foo.bar' => 'bang!',
        ];
        $this->container->set('settings', $settings);

        static::assertTrue($this->container->has('settings.foo.bar'));
        static::assertEquals('bang!', $this->container->get('settings.foo.bar'));

        static::assertFalse($this->container->has('settings.foo.bar.baz'));
        $this->container->get('settings.foo.bar.baz');
    }

    public function testSettingsAccess(): void
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

        static::assertTrue($this->container->has('settings.foo'));
        static::assertEquals(['bar' => ['baz' => 'found!', 'bam' => []]], $this->container->get('settings.foo'));

        static::assertTrue($this->container->has('settings.foo.bar'));
        static::assertEquals(['baz' => 'found!', 'bam' => []], $this->container->get('settings.foo.bar'));

        static::assertTrue($this->container->has('settings.foo.bar.baz'));
        static::assertEquals('found!', $this->container->get('settings.foo.bar.baz'));

        static::assertTrue($this->container->has('settings.foo.bar.bam'));
        static::assertEquals([], $this->container->get('settings.foo.bar.bam'));
    }

    public function testUnresolvable(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Entry "foo" cannot be resolved: the class doesn\'t exist');

        $configuration = new Configuration([
            'definitions' => [
                ['foo' => \DI\create('\\Unknown\\Foo\\Bar')],
            ],
        ]);

        $container = ContainerBuilder::build($configuration);

        $container->get('foo');
    }

    public function testSetterGetter(): void
    {
        $this->container['foo'] = 'bar';
        static::assertTrue($this->container->has('foo'));
        static::assertEquals('bar', $this->container->get('foo'));

        $this->container['bar'] = 'baz';
        static::assertTrue(isset($this->container['bar']));
        static::assertEquals('baz', $this->container['bar']);

        $this->container['baz'] = 'bam';
        static::assertTrue(isset($this->container->baz));
        static::assertEquals('bam', $this->container->baz);

        $this->container->bam = 'foo';
        static::assertTrue($this->container->has('bam'));
        static::assertEquals('foo', $this->container->bam);
    }

    public function testUnset(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('It is not possible to unset container definitions');

        unset($this->container->foo);
    }

    public function testUnsetArray(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('It is not possible to unset container definitions');

        unset($this->container['foo']);
    }

    public function testDefaultServices(): void
    {
        static::assertTrue($this->container->has(Configuration::class));
        static::assertInstanceOf(Configuration::class, $this->container->get(Configuration::class));

        static::assertTrue($this->container->has(ContainerInterface::class));
        static::assertEquals($this->container, $this->container->get(ContainerInterface::class));
    }
}
