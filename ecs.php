<?php

/*
 * (c) 2015-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use PhpCsFixer\Fixer\ArrayNotation\ReturnToYieldFromFixer;
use PhpCsFixer\Fixer\Basic\CurlyBracesPositionFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $header = <<<'HEADER'
    (c) 2015-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/slim-php-di
    HEADER;

    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $ecsConfig->cacheDirectory('.ecs.cache');

    $skipRules = [
        ReturnToYieldFromFixer::class => __DIR__ . '/src/definitions.php',
    ];
    if (\PHP_VERSION_ID < 80_100) {
        $skipRules[CurlyBracesPositionFixer::class] = __DIR__ . '/src/CallableResolver.php';
    }

    (new ConfigSet80())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips($skipRules)
        ->configure($ecsConfig);
};
