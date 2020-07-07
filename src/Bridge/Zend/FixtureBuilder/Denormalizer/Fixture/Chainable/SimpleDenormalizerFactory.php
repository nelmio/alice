<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.specs" />

            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleDenormalizer
    {
        return new SimpleDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.specs'),
        );
    }
}
