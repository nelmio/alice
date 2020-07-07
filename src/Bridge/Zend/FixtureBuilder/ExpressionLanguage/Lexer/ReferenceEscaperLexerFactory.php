<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer;
use Psr\Container\ContainerInterface;

class ReferenceEscaperLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.reference_escaper_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ReferenceEscaperLexer
    {
        return new ReferenceEscaperLexer(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer.globals_patterns_lexer'),
        );
    }
}
