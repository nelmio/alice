<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\ExpressionLanguage\Parser\TokenParser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry;
use Psr\Container\ContainerInterface;

class TokenParserRegistryFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry"
                 class="Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry">
            <!-- Injected via compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): TokenParserRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $tokenParsers = array_map(
            [$container, 'get'],
            $aliceConfig['fixture_builder']['expression_language']['chainable_token_parser']
        );

        return new TokenParserRegistry($tokenParsers);
    }
}
