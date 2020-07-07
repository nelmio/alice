<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedValueTokenParser;
use Psr\Container\ContainerInterface;

class EscapedValueTokemParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.escaped_value_token_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedValueTokenParser">
            <tag name="nelmio_alice.fixture_builder.expression_language.chainable_token_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): EscapedValueTokenParser
    {
        return new EscapedValueTokenParser();
    }
}
