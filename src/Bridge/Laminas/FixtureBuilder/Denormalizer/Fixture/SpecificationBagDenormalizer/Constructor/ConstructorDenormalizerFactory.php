<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorDenormalizer;
use Psr\Container\ContainerInterface;

class ConstructorDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ConstructorDenormalizer
    {
        return new ConstructorDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments'),
        );
    }
}
