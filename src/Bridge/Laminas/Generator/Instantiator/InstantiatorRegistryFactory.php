<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Instantiator;

use Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry;
use Psr\Container\ContainerInterface;

class InstantiatorRegistryFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.registry"
                 class="Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry">
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): InstantiatorRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $instantiators = array_map(
            [$container, 'get'],
            $aliceConfig['generator']['instantiator']['chainable_instantiator']
        );

        return new InstantiatorRegistry($instantiators);
    }
}
