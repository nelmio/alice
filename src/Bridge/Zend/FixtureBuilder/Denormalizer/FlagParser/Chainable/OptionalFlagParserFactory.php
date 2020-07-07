<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser;
use Psr\Container\ContainerInterface;

class OptionalFlagParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.optional"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): OptionalFlagParser
    {
        return new OptionalFlagParser();
    }
}
