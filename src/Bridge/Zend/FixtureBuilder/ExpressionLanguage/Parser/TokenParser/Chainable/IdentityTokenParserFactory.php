<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser;
use Psr\Container\ContainerInterface;

class IdentityTokenParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.identity_token_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): IdentityTokenParser
    {
        return new IdentityTokenParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser')
        );
    }
}
