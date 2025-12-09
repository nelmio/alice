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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ArgumentEscaper;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\DynamicArrayTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedValueTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureListReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureMethodReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureRangeReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FunctionTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\MethodReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\OptionalTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ParameterTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\PropertyReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\SimpleReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringArrayTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\TolerantFunctionTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\WildcardReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.fixture_builder.expression_language.parser.token_parser',
        'nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry',
    );

    $services->set(
        'nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry',
        TokenParserRegistry::class,
        // Arguments injected via compiler pass
    );

    $services->set(
        'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper',
        ArgumentEscaper::class,
    );

    // Chainables
    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.dynamic_array_token_parser',
            DynamicArrayTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.escaped_value_token_parser',
            EscapedValueTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_list_reference_token_parser',
            FixtureListReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_method_reference_token_parser',
            FixtureMethodReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_range_reference_token_parser',
            FixtureRangeReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.method_reference_token_parser',
            MethodReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.optional_token_parser',
            OptionalTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.parameter_token_parser',
            ParameterTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.property_reference_token_parser',
            PropertyReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.variable_reference_token_parser',
            VariableReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.simple_reference_token_parser',
            SimpleReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_array_token_parser',
            StringArrayTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_token_parser',
            StringTokenParser::class,
        )
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper')])
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.tolerant_function_token_parser',
            TolerantFunctionTokenParser::class,
        )
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser')])
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser',
            IdentityTokenParser::class,
        )
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser')]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser',
            FunctionTokenParser::class,
        )
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper')]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.variable_token_parser',
            VariableTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.wildcard_reference_token_parser',
            WildcardReferenceTokenParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');
};
