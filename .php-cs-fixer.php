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

use Jgut\CS\Fixer\FixerConfig74;
use PhpCsFixer\Finder;

$header = <<<'HEADER'
slim-php-di (https://github.com/juliangut/slim-php-di).
Slim Framework PHP-DI container implementation.

@license BSD-3-Clause
@link https://github.com/juliangut/slim-php-di
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

$finder = Finder::create()
    ->ignoreDotFiles(false)
    ->exclude(['build', 'vendor'])
    ->in(__DIR__)
    ->name('.php-cs-fixer.php');

return (new FixerConfig74())
    ->setHeader($header)
    ->enablePhpUnitRules()
    ->setFinder($finder);
