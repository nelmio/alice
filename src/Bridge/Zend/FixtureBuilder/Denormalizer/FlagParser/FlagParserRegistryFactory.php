<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ConfiguratorFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ExtendFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\TemplateFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry;
use Psr\Container\ContainerInterface;

class FlagParserRegistryFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.registry"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry">
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): FlagParserRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $flagParsers = array_map(
            [$container, 'get'],
            $aliceConfig['fixture_builder']['denormalizer']['chainable_flag_parser']
        );

        return new FlagParserRegistry($flagParsers);
    }
}
