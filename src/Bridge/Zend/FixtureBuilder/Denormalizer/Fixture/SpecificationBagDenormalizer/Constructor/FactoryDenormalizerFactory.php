<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer;
use Psr\Container\ContainerInterface;

class FactoryDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FactoryDenormalizer
    {
        return new FactoryDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls'),
        );
    }
}
