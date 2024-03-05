<?php

/*
 * (c) 2015-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$configSet = (new ConfigSet80())
    ->setHeader(<<<'HEADER'
    (c) 2015-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/slim-php-di
    HEADER)
    ->enablePhpUnitRules();
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
