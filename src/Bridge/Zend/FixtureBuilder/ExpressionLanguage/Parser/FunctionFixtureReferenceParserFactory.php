<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FunctionFixtureReferenceParser;
use Psr\Container\ContainerInterface;

class FunctionFixtureReferenceParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.function_fixture_reference_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FunctionFixtureReferenceParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.string_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FunctionFixtureReferenceParser
    {
        return new FunctionFixtureReferenceParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.string_parser'),
        );
    }
}
