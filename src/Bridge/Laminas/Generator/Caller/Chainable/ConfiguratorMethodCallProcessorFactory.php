<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Caller\Chainable;

use Nelmio\Alice\Generator\Caller\Chainable\ConfiguratorMethodCallProcessor;
use Psr\Container\ContainerInterface;

class ConfiguratorMethodCallProcessorFactory
{
    /*
        <service id="nelmio_alice.generator.caller.chainable.configurator_method_call"
                 class="Nelmio\Alice\Generator\Caller\Chainable\ConfiguratorMethodCallProcessor">
            <tag name="nelmio_alice.generator.caller.chainable_call_processor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ConfiguratorMethodCallProcessor
    {
        return new ConfiguratorMethodCallProcessor();
    }
}
