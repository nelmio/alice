<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\TolerantFunctionTokenParser;
use Psr\Container\ContainerInterface;

class TolerantFunctionTokemParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.tolerant_function_token_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\TolerantFunctionTokenParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser" />

            <tag name="nelmio_alice.fixture_builder.expression_language.chainable_token_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): TolerantFunctionTokenParser
    {
        return new TolerantFunctionTokenParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser')
        );
    }
}
