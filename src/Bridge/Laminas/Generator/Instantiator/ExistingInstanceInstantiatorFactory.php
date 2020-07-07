<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Instantiator;

use Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator;
use Psr\Container\ContainerInterface;

class ExistingInstanceInstantiatorFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.existing_instance"
                 class="Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator">
            <argument type="service" id="nelmio_alice.generator.instantiator.resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ExistingInstanceInstantiator
    {
        return new ExistingInstanceInstantiator(
            $container->get('nelmio_alice.generator.instantiator.resolver')
        );
    }
}
