<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\StringMergerParser;
use Psr\Container\ContainerInterface;

class StringMergerParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.string_parser"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\StringMergerParser">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser.simple_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): StringMergerParser
    {
        return new StringMergerParser(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser.simple_parser'),
        );
    }
}
