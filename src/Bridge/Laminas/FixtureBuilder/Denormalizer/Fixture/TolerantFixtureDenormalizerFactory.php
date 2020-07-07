<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\TolerantFixtureDenormalizer;
use Psr\Container\ContainerInterface;

class TolerantFixtureDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.tolerant_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\TolerantFixtureDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): TolerantFixtureDenormalizer
    {
        return new TolerantFixtureDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer')
        );
    }
}
