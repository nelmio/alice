<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer;
use Psr\Container\ContainerInterface;

class FunctionLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.function_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FunctionLexer
    {
        return new FunctionLexer(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer.string_then_reference_lexer'),
        );
    }
}
