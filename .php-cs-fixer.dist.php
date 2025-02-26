<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Fidry\PhpCsFixerConfig\FidryConfig;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__.'/fixtures',
        __DIR__.'/profiling',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->append([
        __DIR__.'/.php-cs-fixer.dist.php',
    ])
    ->exclude([
        'fixtures/Parser/files/php',
        'Bridge/Symfony/Application/var',
        'fixtures/Bridge/Symfony/Application/cache',
    ]);

$config = new FidryConfig(
    <<<'EOF'
        This file is part of the Alice package.

        (c) Nelmio <hello@nelm.io>

        For the full copyright and license information, please view the LICENSE
        file that was distributed with this source code.
        EOF,
    74_000,
);

$config->addRules([
    'php_unit_method_casing' => ['case' => 'camel_case'],
    'php_unit_test_annotation' => false,
    'phpdoc_no_empty_return' => false,
    'static_lambda' => false,
    'php_unit_data_provider_method_order' => false,
]);

$config->setFinder($finder);
$config->setCacheFile(__DIR__.'/dist/.php-cs-fixer.cache');

return $config;
