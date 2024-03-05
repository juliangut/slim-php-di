<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use PhpCsFixer\Fixer\Basic\CurlyBracesPositionFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$skips = [];
if (\PHP_VERSION_ID < 80_100) {
    $skips[CurlyBracesPositionFixer::class] = __DIR__ . '/src/CallableResolver.php';
}

$configSet = (new ConfigSet80())
    ->setHeader(<<<'HEADER'
    (c) 2015-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/slim-php-di
    HEADER)
    ->enablePhpUnitRules()
    ->setAdditionalSkips($skips);
$paths = [
    __FILE__,
    __DIR__ . '/src',
    __DIR__ . '/tests',
];

if (!method_exists(ECSConfig::class, 'configure')) {
    return static function (ECSConfig $ecsConfig) use ($configSet, $paths): void {
        $ecsConfig->paths($paths);

        $configSet->configure($ecsConfig);
    };
}

return $configSet
    ->configureBuilder()
    ->withPaths($paths);
