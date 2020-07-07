<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer;
use Psr\Container\ContainerInterface;

class SubPatternsLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.sub_patterns_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SubPatternsLexer
    {
        return new SubPatternsLexer(
            $container->get('nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer'),
        );
    }
}
