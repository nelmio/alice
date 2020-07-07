<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ParameterTokenParser;
use Psr\Container\ContainerInterface;

class ParameterTokemParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.parameter_token_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ParameterTokenParser">
            <tag name="nelmio_alice.fixture_builder.expression_language.chainable_token_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ParameterTokenParser
    {
        return new ParameterTokenParser();
    }
}
