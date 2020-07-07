<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer;
use Psr\Container\ContainerInterface;

class NullListNameDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer">
        </service>
    */
    public function __invoke(ContainerInterface $container): NullListNameDenormalizer
    {
        return new NullListNameDenormalizer();
    }
}
