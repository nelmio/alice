<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser;
use Psr\Container\ContainerInterface;

class ElementFlagParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.element"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.flag_parser.registry" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ElementFlagParser
    {
        return new ElementFlagParser(
            $container->get('nelmio_alice.fixture_builder.denormalizer.flag_parser.registry')
        );
    }
}
