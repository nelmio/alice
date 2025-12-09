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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\StringThenReferenceLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.fixture_builder.expression_language.lexer',
        'nelmio_alice.fixture_builder.expression_language.lexer.empty_value_lexer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.lexer.empty_value_lexer',
            EmptyValueLexer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer',
            ReferenceEscaperLexer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer',
            GlobalPatternsLexer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer.function_lexer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.lexer.function_lexer',
            FunctionLexer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer',
            StringThenReferenceLexer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer',
            SubPatternsLexer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer'),
        ]);

    $services->set(
        'nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer',
        ReferenceLexer::class,
    );
};
