<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullRangeNameDenormalizer;
use Psr\Container\ContainerInterface;

class NullRangeNameDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_range"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullRangeNameDenormalizer">
        </service>
    */
    public function __invoke(ContainerInterface $container): NullRangeNameDenormalizer
    {
        return new NullRangeNameDenormalizer();
    }
}
