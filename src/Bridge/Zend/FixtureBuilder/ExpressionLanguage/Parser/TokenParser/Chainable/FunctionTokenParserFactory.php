<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FunctionTokenParser;
use Psr\Container\ContainerInterface;

class FunctionTokenParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.function_token_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FunctionTokenParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FunctionTokenParser
    {
        return new FunctionTokenParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper')
        );
    }
}
