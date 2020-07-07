<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\CallsWithFlagsDenormalizer;
use Psr\Container\ContainerInterface;

class CallsWithFlagsDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\CallsWithFlagsDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer" />
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): CallsWithFlagsDenormalizer
    {
        return new CallsWithFlagsDenormalizer(
            $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer'),
            [
                $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.configurator_flag_handler'),
                $container->get('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.optional_flag_handler'),
            ]
        );
    }
}
