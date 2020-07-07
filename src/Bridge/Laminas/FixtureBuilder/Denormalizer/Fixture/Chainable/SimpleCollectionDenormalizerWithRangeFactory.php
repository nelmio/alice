<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleCollectionDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleCollectionDenormalizerWithRangeFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_range"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleCollectionDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_range" />

            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleCollectionDenormalizer
    {
        return new SimpleCollectionDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_range'),
        );
    }
}
