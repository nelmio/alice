<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleArgumentsDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments.simple_arguments_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.value" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleArgumentsDenormalizer
    {
        return new SimpleArgumentsDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value'),
        );
    }
}
