<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ExtendFlagParser;
use Psr\Container\ContainerInterface;

class ExtendFlagParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.extend"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ExtendFlagParser">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ExtendFlagParser
    {
        return new ExtendFlagParser();
    }
}
