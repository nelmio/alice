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

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.fixture_builder.expression_language.lexer', 'nelmio_alice.fixture_builder.expression_language.lexer.empty_value_lexer');

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.empty_value_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.lexer.function_lexer')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.function_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\StringThenReferenceLexer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer::class);
};
