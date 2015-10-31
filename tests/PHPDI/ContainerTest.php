<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDI\Tests;

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
     * @expectedException \Slim\Exception\ContainerValueNotFoundException
     */
    public function testGetInexistent()
    {
        $this->container['foo'];
    }

    /**
     * @covers Jgut\Slim\PHPDI\Container::get
     *
     * @expectedException \Slim\Exception\ContainerValueNotFoundException
     */
    public function testGetWrong()
    {
        $this->container->get(0);
    }

    /**
     * @covers Jgut\Slim\PHPDI\Container::get
     * @covers Jgut\Slim\PHPDI\Container::offsetSet
     * @covers Jgut\Slim\PHPDI\Container::offsetExists
     * @covers Jgut\Slim\PHPDI\Container::offsetGet
     */
    public function testSetGet()
    {
        $this->container['foo'] = 'bar';
        $this->assertTrue($this->container->has('foo'));
        $this->assertEquals('bar', $this->container->get('foo'));

        $this->container['bar'] = 'baz';
        $this->assertTrue(isset($this->container['bar']));
        $this->assertEquals('baz', $this->container['bar']);

        // Doesn't really work
        unset($this->container['foo']);
    }

    /**
     * Check container has default services.
     */
    public function testDefaultServices()
    {
        $this->assertTrue($this->container->has('settings'));
        $this->assertTrue($this->container->has('environment'));
        $this->assertTrue($this->container->has('request'));
        $this->assertTrue($this->container->has('response'));
        $this->assertTrue($this->container->has('router'));
        $this->assertTrue($this->container->has('foundHandler'));
        $this->assertTrue($this->container->has('errorHandler'));
        $this->assertTrue($this->container->has('notFoundHandler'));
        $this->assertTrue($this->container->has('notAllowedHandler'));
        $this->assertTrue($this->container->has('callableResolver'));
    }

    /**
     * Test default servicesType
     */
    public function testDefaultServicesType()
    {
        $this->assertInstanceOf('\Slim\Collection', $this->container->get('settings'));
        $this->assertInstanceOf('\Slim\Http\Environment', $this->container->get('environment'));
        $this->assertInstanceOf('\Slim\Http\Environment', $this->container->get('environment'));
        $this->assertInstanceOf('\Psr\Http\Message\RequestInterface', $this->container->get('request'));
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $this->container->get('response'));
        $this->assertInstanceOf('\Slim\Interfaces\RouterInterface', $this->container->get('router'));
        $this->assertInstanceOf('\Slim\Handlers\Strategies\RequestResponse', $this->container['foundHandler']);
        $this->assertInstanceOf('\Slim\Handlers\Error', $this->container->get('errorHandler'));
        $this->assertInstanceOf('\Slim\Handlers\NotFound', $this->container->get('notFoundHandler'));
        $this->assertInstanceOf('\Slim\Handlers\NotAllowed', $this->container->get('notAllowedHandler'));
        $this->assertInstanceOf(
            '\Slim\Interfaces\CallableResolverInterface',
            $this->container->get('callableResolver')
        );
    }
}
