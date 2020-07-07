<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder;

use Nelmio\Alice\FixtureBuilder\SimpleBuilder;
use Psr\Container\ContainerInterface;

class SimpleBuilderFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.simple"
                 class="Nelmio\Alice\FixtureBuilder\SimpleBuilder">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleBuilder
    {
        return new SimpleBuilder(
            $container->get('nelmio_alice.fixture_builder.denormalizer'),
        );
    }
}
