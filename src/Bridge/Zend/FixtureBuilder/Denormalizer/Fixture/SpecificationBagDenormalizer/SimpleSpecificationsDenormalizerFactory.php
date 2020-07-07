<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleSpecificationsDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.specs.simple"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor" />
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.property" />
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls" />
        </service>

    */
    public function __invoke(ContainerInterface $container): SimpleSpecificationsDenormalizer
    {
        return new SimpleSpecificationsDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor'),
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.property'),
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls'),
        );
    }
}
