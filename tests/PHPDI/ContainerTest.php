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

use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Jgut\Slim\PHPDI\FoundHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Error;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\NotFound;
use Slim\Handlers\PhpError;
use Slim\Http\Environment;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouterInterface;

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
        $this->container = ContainerBuilder::build(new Configuration);
    }

    /**
     * @expectedException \Slim\Exception\ContainerValueNotFoundException
     */
    public function testGetNonExistent()
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

        // Doesn't really work
        unset($this->container['foo']);
    }

    public function testDefaultServices()
    {
        self::assertTrue($this->container->has('settings'));
        self::assertInternalType('array', $this->container->get('settings'));

        self::assertTrue($this->container->has('environment'));
        self::assertInstanceOf(Environment::class, $this->container->get('environment'));

        self::assertTrue($this->container->has('request'));
        self::assertInstanceOf(ServerRequestInterface::class, $this->container->get('request'));

        self::assertTrue($this->container->has('response'));
        self::assertInstanceOf(ResponseInterface::class, $this->container->get('response'));

        self::assertTrue($this->container->has('router'));
        self::assertInstanceOf(RouterInterface::class, $this->container->get('router'));

        self::assertTrue($this->container->has('phpErrorHandler'));
        self::assertInstanceOf(PhpError::class, $this->container->get('phpErrorHandler'));

        self::assertTrue($this->container->has('errorHandler'));
        self::assertInstanceOf(Error::class, $this->container->get('errorHandler'));

        self::assertTrue($this->container->has('notFoundHandler'));
        self::assertInstanceOf(NotFound::class, $this->container->get('notFoundHandler'));

        self::assertTrue($this->container->has('notAllowedHandler'));
        self::assertInstanceOf(NotAllowed::class, $this->container->get('notAllowedHandler'));

        self::assertTrue($this->container->has('foundHandler'));
        self::assertInstanceOf(FoundHandler::class, $this->container['foundHandler']);

        self::assertTrue($this->container->has('callableResolver'));
        self::assertInstanceOf(CallableResolverInterface::class, $this->container->get('callableResolver'));
    }
}
