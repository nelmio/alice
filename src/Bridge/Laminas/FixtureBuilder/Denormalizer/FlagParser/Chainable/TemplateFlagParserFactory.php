<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\TemplateFlagParser;
use Psr\Container\ContainerInterface;

class TemplateFlagParserFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.template"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\TemplateFlagParser">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): TemplateFlagParser
    {
        return new TemplateFlagParser();
    }
}
