<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Tests;

use DI\Container;
use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function DI\create;

/**
 * @internal
 */
class ContainerTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = ContainerBuilder::build();
    }

    public function testGetNonExistent(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('No entry or class found for "baz".');

        static::assertFalse($this->container->has('baz'));
        $this->container->get('baz');
    }

    public function testGetNonExistentRecursive(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('No entry or class found for "settings.baz".');

        static::assertFalse($this->container->has('settings.baz'));
        $this->container->get('settings.baz');
    }

    public function testGetShadowed(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('No entry or class found for "settings.foo.bar.baz".');

        $settings = [
            'foo' => [
                'bar' => [
                    'baz' => 'shadowed!',
                ],
            ],
            'foo.bar' => 'bingo!',
        ];
        $this->container->set('settings', $settings);

        static::assertTrue($this->container->has('settings.foo.bar'));
        static::assertEquals('bingo!', $this->container->get('settings.foo.bar'));

        static::assertFalse($this->container->has('settings.foo.bar.baz'));
        $this->container->get('settings.foo.bar.baz');
    }

    public function testRecursiveAccess(): void
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
        static::assertEquals(
            [
                'bar' => [
                    'baz' => 'found!',
                    'bam' => [],
                ],
            ],
            $this->container->get('settings.foo'),
        );

        static::assertTrue($this->container->has('settings.foo.bar'));
        static::assertEquals(
            [
                'baz' => 'found!',
                'bam' => [],
            ],
            $this->container->get('settings.foo.bar'),
        );

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
                ['foo' => create('\\Unknown\\Foo\\Bar')],
            ],
        ]);

        $container = ContainerBuilder::build($configuration);

        $container->get('foo');
    }

    public function testSetterGetter(): void
    {
        $this->container->set('foo', 'bar');
        static::assertTrue($this->container->has('foo'));
        static::assertEquals('bar', $this->container->get('foo'));
    }

    public function testDefaultServices(): void
    {
        static::assertTrue($this->container->has(ResponseFactoryInterface::class));
        static::assertInstanceOf(ResponseFactory::class, $this->container->get(ResponseFactoryInterface::class));

        static::assertTrue($this->container->has(StreamFactoryInterface::class));
        static::assertInstanceOf(StreamFactory::class, $this->container->get(StreamFactoryInterface::class));

        static::assertTrue($this->container->has(Configuration::class));
        static::assertInstanceOf(Configuration::class, $this->container->get(Configuration::class));

        static::assertTrue($this->container->has(ContainerInterface::class));
        static::assertEquals($this->container, $this->container->get(ContainerInterface::class));
    }
}
