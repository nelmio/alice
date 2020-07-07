<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer;
use Psr\Container\ContainerInterface;

class ReferenceLexerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.lexer.reference_lexer"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer">
        </service>
    */
    public function __invoke(ContainerInterface $container): ReferenceLexer
    {
        return new ReferenceLexer();
    }
}
