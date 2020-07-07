<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry;
use Psr\Container\ContainerInterface;

class FixtureDenormalizerRegistryFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.flag_parser" />
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): FixtureDenormalizerRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $fixtureDenormalizers = array_map(
            [$container, 'get'],
            $aliceConfig['fixture_builder']['denormalizer']['chainable_fixture_denormalizer']
        );
        return new FixtureDenormalizerRegistry(
            $container->get('nelmio_alice.fixture_builder.denormalizer.flag_parser'),
            $fixtureDenormalizers
        );
    }
}
