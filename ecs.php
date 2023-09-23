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

use Jgut\ECS\Config\ConfigSet81;
use PhpCsFixer\Fixer\ArrayNotation\ReturnToYieldFromFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$header = <<<'HEADER'
slim-php-di (https://github.com/juliangut/slim-php-di).
Slim Framework PHP-DI container implementation.

@license BSD-3-Clause
@link https://github.com/juliangut/slim-php-di
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

return static function (ECSConfig $ecsConfig) use ($header): void {
    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    (new ConfigSet81())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips([
            ReturnToYieldFromFixer::class => __DIR__ . '/src/definitions.php', // Fails on file-wide array return
        ])
        ->configure($ecsConfig);
};
