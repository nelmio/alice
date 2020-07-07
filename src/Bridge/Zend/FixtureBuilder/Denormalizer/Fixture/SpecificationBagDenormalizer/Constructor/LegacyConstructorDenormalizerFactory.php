<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\LegacyConstructorDenormalizer;
use Psr\Container\ContainerInterface;

class LegacyConstructorDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.legacy_constructor_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\LegacyConstructorDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer" />
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): LegacyConstructorDenormalizer
    {
        return new LegacyConstructorDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer'),
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer'),
        );
    }
}
