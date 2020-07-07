<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer;
use Psr\Container\ContainerInterface;

class EmptyValueLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.empty_value_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer">

            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): EmptyValueLexer
    {
        return new EmptyValueLexer(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer'),
        );
    }
}
