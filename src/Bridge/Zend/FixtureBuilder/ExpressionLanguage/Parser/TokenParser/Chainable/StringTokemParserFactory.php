<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringTokenParser;
use Psr\Container\ContainerInterface;

class StringTokemParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_token_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringTokenParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper" />

            <tag name="nelmio_alice.fixture_builder.expression_language.chainable_token_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): StringTokenParser
    {
        return new StringTokenParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper')
        );
    }
}
