<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ArgumentEscaper;
use Psr\Container\ContainerInterface;

class ArgumentEscaperFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.argument_escaper"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ArgumentEscaper" />
    */
    public function __invoke(ContainerInterface $container): ArgumentEscaper
    {
        return new ArgumentEscaper();
    }
}
