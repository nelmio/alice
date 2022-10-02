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

use PhpCsFixer\Config;
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
    ])
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => true,
        'cast_spaces' => true,
        'combine_consecutive_unsets' => true,
        'declare_equal_normalize' => true,
        'declare_strict_types' => true,
        'heredoc_to_nowdoc' => true,
        'include' => true,
        'header_comment' => [
            'location' => 'after_open',
            'header' => <<<'LICENSE'
                This file is part of the Alice package.
                
                (c) Nelmio <hello@nelm.io>
                
                For the full copyright and license information, please view the LICENSE
                file that was distributed with this source code.
                LICENSE,
        ],
        'lowercase_cast' => true,
        'general_phpdoc_annotation_remove' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'modernize_types_casting' => true,
        'native_function_casing' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_phpdoc_tags' => [
            'remove_inheritdoc' => true,
        ],
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_order' => true,
        'phpdoc_order_by_value' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'php_unit_expectation' => true,
        'php_unit_fqcn_annotation' => true,
        'php_unit_namespaced' => true,
        'php_unit_test_case_static_method_calls' => true,
        'php_unit_test_class_requires_covers' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'trim_array_spaces' => true,
        'void_return' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder)
;
