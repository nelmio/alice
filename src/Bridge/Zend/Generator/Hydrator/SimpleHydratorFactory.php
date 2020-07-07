<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Hydrator;

use Nelmio\Alice\Generator\Hydrator\SimpleHydrator;
use Psr\Container\ContainerInterface;

class SimpleHydratorFactory
{
    /*
        <service id="nelmio_alice.generator.hydrator.simple"
                 class="Nelmio\Alice\Generator\Hydrator\SimpleHydrator">
            <argument type="service" id="nelmio_alice.generator.hydrator.property" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleHydrator
    {
        return new SimpleHydrator(
            $container->get('nelmio_alice.generator.hydrator.property')
        );
    }
}
