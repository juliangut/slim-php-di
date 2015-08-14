<?php
/**
 * Slim Framework PHP-DI container (https://github.com/juliangut/slim-php-di)
 *
 * @link https://github.com/juliangut/slim-php-di for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-php-di/master/LICENSE
 */

namespace Jgut\Slim\PHPDITests;

use Jgut\Slim\PHPDI\ContainerBuilder;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Jgut\Slim\PHPDI\ContainerBuilder::build
     */
    public function testBuild()
    {
        $settings = [
            'php-di' => [
                'use_autowiring' => false,
                'use_annotations' => true,
                'ignore_phpdoc_errors' => true,
                'proxy_path' => 'fake/path',
                'definitions' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        ContainerBuilder::build($settings);
    }
}
