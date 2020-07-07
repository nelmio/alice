<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\StringThenReferenceLexer;
use Psr\Container\ContainerInterface;

class StringThenReferenceLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\StringThenReferenceLexer">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): StringThenReferenceLexer
    {
        return new StringThenReferenceLexer(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer'),
        );
    }
}
