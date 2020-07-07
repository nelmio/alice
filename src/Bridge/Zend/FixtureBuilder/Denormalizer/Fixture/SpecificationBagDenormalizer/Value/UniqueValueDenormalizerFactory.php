<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer;
use Psr\Container\ContainerInterface;

class UniqueValueDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.unique_value_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): UniqueValueDenormalizer
    {
        return new UniqueValueDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer')
        );
    }
}
