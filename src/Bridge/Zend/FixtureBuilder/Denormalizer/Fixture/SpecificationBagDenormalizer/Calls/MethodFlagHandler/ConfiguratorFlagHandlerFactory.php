<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\ConfiguratorFlagHandler;
use Psr\Container\ContainerInterface;

class ConfiguratorFlagHandlerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.configurator_flag_handler"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\ConfiguratorFlagHandler">
            <tag name="nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ConfiguratorFlagHandler
    {
        return new ConfiguratorFlagHandler();
    }
}
