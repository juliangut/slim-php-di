<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDITests;

use Jgut\Slim\PHPDI\ContainerBuilder;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $container;

    /**
     * @covers Jgut\Slim\PHPDI\Container::registerDefaultServices
     */
    public function setUp()
    {
        $this->container = ContainerBuilder::build();
    }

    /**
     * @covers Jgut\Slim\PHPDI\Container::offsetGet
     * @covers Jgut\Slim\PHPDI\Container::get
     *
     * @expectedException \Slim\Exception\NotFoundException
     */
    public function testGetInexistent()
    {
        $this->container['foo'];
    }

    /**
     * @covers Jgut\Slim\PHPDI\Container::get
     *
     * @expectedException \Slim\Exception\NotFoundException
     */
    public function testGetWrong()
    {
        $this->container->get(0);
    }

    /**
     * @covers Jgut\Slim\PHPDI\Container::set
     * @covers Jgut\Slim\PHPDI\Container::set
     * @covers Jgut\Slim\PHPDI\Container::offsetSet
     * @covers Jgut\Slim\PHPDI\Container::offsetGet
     */
    public function testSet()
    {
        $this->container->set('foo', 'bar');
        $this->assertEquals('bar', $this->container->get('foo'));

        $this->container['bar'] = 'baz';
        $this->assertEquals('baz', $this->container['bar']);
    }

    /**
     * Check container has default services.
     *
     * @covers Jgut\Slim\PHPDI\Container::has
     * @covers Jgut\Slim\PHPDI\Container::offsetExists
     */
    public function testGet()
    {
        $this->assertTrue($this->container->has('environment'));
        $this->assertTrue(isset($this->container['request']));
        $this->assertTrue($this->container->has('response'));
        $this->assertTrue(isset($this->container['router']));
        $this->assertTrue($this->container->has('foundHandler'));
        $this->assertTrue(isset($this->container['errorHandler']));
        $this->assertTrue($this->container->has('notFoundHandler'));
        $this->assertTrue(isset($this->container['notAllowedHandler']));
        $this->assertTrue($this->container->has('callableResolver'));
    }

    /**
     * Check container default services type.
     *
     * @covers Jgut\Slim\PHPDI\Container::registerDefaultServices
     * @covers Jgut\Slim\PHPDI\Container::get
     * @covers Jgut\Slim\PHPDI\Container::offsetGet
     */
    public function testServicesType()
    {
        $this->assertInstanceOf('\Slim\Http\Environment', $this->container->get('environment'));
        $this->assertInstanceOf('\Psr\Http\Message\RequestInterface', $this->container['request']);
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $this->container['response']);
        $this->assertInstanceOf('\Slim\Interfaces\RouterInterface', $this->container['router']);
        $this->assertInstanceOf('\Slim\Handlers\Strategies\RequestResponse', $this->container['foundHandler']);
        $this->assertInstanceOf('\Slim\Handlers\Error', $this->container['errorHandler']);
        $this->assertInstanceOf('\Slim\Handlers\NotFound', $this->container['notFoundHandler']);
        $this->assertInstanceOf('\Slim\Handlers\NotAllowed', $this->container['notAllowedHandler']);
        $this->assertInstanceOf('\Slim\Interfaces\CallableResolverInterface', $this->container['callableResolver']);
    }
}
