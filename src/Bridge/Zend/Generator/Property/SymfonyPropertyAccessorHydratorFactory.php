<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Property;

use Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator;
use Psr\Container\ContainerInterface;

class SymfonyPropertyAccessorHydratorFactory
{
    /*
        <service id="nelmio_alice.generator.hydrator.property.symfony_property_access"
                 class="Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator">
            <argument type="service" id="nelmio_alice.property_accessor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SymfonyPropertyAccessorHydrator
    {
        return new SymfonyPropertyAccessorHydrator(
            $container->get('nelmio_alice.property_accessor')
        );
    }
}
