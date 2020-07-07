<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer;
use Psr\Container\ContainerInterface;

class ReferenceRangeNameDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.reference_range_name"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.specs.simple" />

            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ReferenceRangeNameDenormalizer
    {
        return new ReferenceRangeNameDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.specs.simple'),
        );
    }
}
