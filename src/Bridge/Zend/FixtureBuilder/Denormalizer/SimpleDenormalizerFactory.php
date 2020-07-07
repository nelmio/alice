<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.simple"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.parameter_bag" />
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture_bag" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleDenormalizer
    {
        return new SimpleDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.parameter_bag'),
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture_bag')
        );
    }
}
