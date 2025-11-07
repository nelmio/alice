<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.fixture_builder.expression_language.parser.token_parser', 'nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry::class);

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ArgumentEscaper::class);

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.dynamic_array_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\DynamicArrayTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.escaped_value_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedValueTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_list_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureListReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_method_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureMethodReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_range_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureRangeReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.method_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\MethodReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.optional_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\OptionalTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.parameter_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ParameterTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.property_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\PropertyReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.variable_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.simple_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\SimpleReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_array_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringArrayTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringTokenParser::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper')])
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.tolerant_function_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\TolerantFunctionTokenParser::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser')])
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FunctionTokenParser::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.variable_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.wildcard_reference_token_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\WildcardReferenceTokenParser::class)
        ->tag('nelmio_alice.fixture_builder.expression_language.chainable_token_parser');
};
