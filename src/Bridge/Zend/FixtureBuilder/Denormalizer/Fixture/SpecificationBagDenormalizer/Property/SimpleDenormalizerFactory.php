<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.property.simple_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.value" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimplePropertyDenormalizer
    {
        return new SimplePropertyDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value')
        );
    }
}
