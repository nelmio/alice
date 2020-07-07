<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleFixtureBagDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.simple_fixture_bag_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture" />
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.flag_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleFixtureBagDenormalizer
    {
        return new SimpleFixtureBagDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture'),
            $container->get('nelmio_alice.fixture_builder.denormalizer.flag_parser')
        );
    }
}
