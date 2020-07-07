<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ConfiguratorFlagParser;
use Psr\Container\ContainerInterface;

class ConfiguratorFlagParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.configurator"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ConfiguratorFlagParser">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ConfiguratorFlagParser
    {
        return new ConfiguratorFlagParser();
    }
}
