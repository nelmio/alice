<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser;
use Psr\Container\ContainerInterface;

class SimpleParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.simple_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer" />
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.token_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleParser
    {
        return new SimpleParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer'),
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.token_parser')
        );
    }
}
