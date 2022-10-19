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

use Jgut\ECS\Config\ConfigSet74;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\NoSilencedErrorsSniff;
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

    (new ConfigSet74())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips([
            NoSilencedErrorsSniff::class . '.Discouraged' => [
                __DIR__ . '/src/ContainerTrait.php', // Temporal while deprecating container's ArrayAccess
            ],
        ])
        ->configure($ecsConfig);
};
