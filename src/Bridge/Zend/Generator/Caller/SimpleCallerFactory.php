<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Caller;

use Nelmio\Alice\Generator\Caller\SimpleCaller;
use Psr\Container\ContainerInterface;

class SimpleCallerFactory
{
    /*
        <service id="nelmio_alice.generator.caller.simple"
                 class="Nelmio\Alice\Generator\Caller\SimpleCaller">
            <argument type="service" id="nelmio_alice.generator.caller.registry" />
            <argument type="service" id="nelmio_alice.generator.resolver.value" />
            <argument type="service" id="nelmio_alice.generator.named_arguments_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleCaller
    {
        return new SimpleCaller(
            $container->get('nelmio_alice.generator.caller.registry'),
            $container->get('nelmio_alice.generator.resolver.value'),
            $container->get('nelmio_alice.generator.named_arguments_resolver')
        );
    }
}
