<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\OptionalFlagHandler;
use Psr\Container\ContainerInterface;

class OptionalFlagHandlerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.optional_flag_handler"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\OptionalFlagHandler">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler" />
        </service>
    */
    public function __invoke(ContainerInterface $container): OptionalFlagHandler
    {
        return new OptionalFlagHandler();
    }
}
