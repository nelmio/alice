<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Instantiator;

use Nelmio\Alice\Generator\Instantiator\InstantiatorResolver;
use Psr\Container\ContainerInterface;

class InstantiatorResolverFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.resolver"
                 class="Nelmio\Alice\Generator\Instantiator\InstantiatorResolver">
            <argument type="service" id="nelmio_alice.generator.instantiator.registry" />
        </service>
    */
    public function __invoke(ContainerInterface $container): InstantiatorResolver
    {
        return new InstantiatorResolver(
            $container->get('nelmio_alice.generator.instantiator.registry')
        );
    }
}
