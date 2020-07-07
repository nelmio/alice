<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\CollectionDenormalizerWithTemporaryFixture;
use Psr\Container\ContainerInterface;

class CollectionDenormalizerWithTemporaryFixtureWithListFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_list"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\CollectionDenormalizerWithTemporaryFixture">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list" />
        </service>
    */
    public function __invoke(ContainerInterface $container): CollectionDenormalizerWithTemporaryFixture
    {
        return new CollectionDenormalizerWithTemporaryFixture(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list'),
        );
    }
}
