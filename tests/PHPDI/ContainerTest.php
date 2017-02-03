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

use Jgut\Slim\PHPDI\ContainerBuilder;

/**
 * Container tests.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /* @var \Jgut\Slim\PHPDI\Container */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->container = ContainerBuilder::build();
    }

    /**
     * @expectedException \Slim\Exception\ContainerValueNotFoundException
     */
    public function testGetInexistent()
    {
        $this->container['foo'];
    }

    /**
     * @expectedException \Slim\Exception\ContainerValueNotFoundException
     */
    public function testGetWrong()
    {
        $this->container->get(0);
    }

    public function testSetGet()
    {
        $this->container['foo'] = 'bar';
        self::assertTrue($this->container->has('foo'));
        self::assertEquals('bar', $this->container->get('foo'));

        $this->container['bar'] = 'baz';
        self::assertTrue(isset($this->container['bar']));
        self::assertEquals('baz', $this->container['bar']);

        $this->container['baz'] = 'foo';
        self::assertTrue(isset($this->container->baz));
        self::assertEquals('foo', $this->container->baz);

        // Doesn't really work
        unset($this->container['foo']);
    }

    public function testDefaultServices()
    {
        self::assertTrue($this->container->has('settings'));
        self::assertTrue($this->container->has('environment'));
        self::assertTrue($this->container->has('request'));
        self::assertTrue($this->container->has('response'));
        self::assertTrue($this->container->has('router'));
        self::assertTrue($this->container->has('foundHandler'));
        self::assertTrue($this->container->has('phpErrorHandler'));
        self::assertTrue($this->container->has('errorHandler'));
        self::assertTrue($this->container->has('notFoundHandler'));
        self::assertTrue($this->container->has('notAllowedHandler'));
        self::assertTrue($this->container->has('callableResolver'));
    }

    public function testDefaultServicesType()
    {
        self::assertInstanceOf('\Slim\Collection', $this->container->get('settings'));
        self::assertInstanceOf('\Slim\Http\Environment', $this->container->get('environment'));
        self::assertInstanceOf('\Slim\Http\Environment', $this->container->get('environment'));
        self::assertInstanceOf('\Psr\Http\Message\RequestInterface', $this->container->get('request'));
        self::assertInstanceOf('\Psr\Http\Message\ResponseInterface', $this->container->get('response'));
        self::assertInstanceOf('\Slim\Interfaces\RouterInterface', $this->container->get('router'));
        self::assertInstanceOf('\Slim\Handlers\Strategies\RequestResponse', $this->container['foundHandler']);
        self::assertInstanceOf('\Slim\Handlers\PhpError', $this->container->get('phpErrorHandler'));
        self::assertInstanceOf('\Slim\Handlers\Error', $this->container->get('errorHandler'));
        self::assertInstanceOf('\Slim\Handlers\NotFound', $this->container->get('notFoundHandler'));
        self::assertInstanceOf('\Slim\Handlers\NotAllowed', $this->container->get('notAllowedHandler'));
        self::assertInstanceOf(
            '\Slim\Interfaces\CallableResolverInterface',
            $this->container->get('callableResolver')
        );
    }
}
