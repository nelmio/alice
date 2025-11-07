<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.fixture_builder.expression_language.parser', 'nelmio_alice.fixture_builder.expression_language.parser.function_fixture_reference_parser');

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.function_fixture_reference_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FunctionFixtureReferenceParser::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.string_parser')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.string_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\StringMergerParser::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser.simple_parser')]);

    $services->set('nelmio_alice.fixture_builder.expression_language.parser.simple_parser', \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser::class)
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.lexer'),
            service('nelmio_alice.fixture_builder.expression_language.parser.token_parser'),
        ]);
};
