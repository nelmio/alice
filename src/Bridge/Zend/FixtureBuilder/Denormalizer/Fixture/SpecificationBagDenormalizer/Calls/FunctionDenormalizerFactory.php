<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer;
use Psr\Container\ContainerInterface;

class FunctionDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FunctionDenormalizer
    {
        return new FunctionDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments')
        );
    }
}
