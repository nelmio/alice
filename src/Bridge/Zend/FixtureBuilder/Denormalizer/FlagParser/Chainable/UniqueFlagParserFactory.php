<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser;
use Psr\Container\ContainerInterface;

class UniqueFlagParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.unique"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): UniqueFlagParser
    {
        return new UniqueFlagParser();
    }
}
