<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer;
use Psr\Container\ContainerInterface;

class GlobalsPatternsLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer">

            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer.function_lexer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): GlobalPatternsLexer
    {
        return new GlobalPatternsLexer(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer.function_lexer'),
        );
    }
}
