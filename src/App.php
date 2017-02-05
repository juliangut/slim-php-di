<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Slim\PHPDI;

use Slim\App as SlimApp;

/**
 * Slim App replacement with PHP-DI container.
 */
class App extends SlimApp
{
    /**
     * App constructor.
     *
     * @param Configuration $configuration
     *
     * @throws \RuntimeException
     */
    public function __construct(Configuration $configuration = null)
    {
        if ($configuration === null) {
            $configuration = new Configuration();
        }

        parent::__construct(ContainerBuilder::build($configuration));
    }
}
