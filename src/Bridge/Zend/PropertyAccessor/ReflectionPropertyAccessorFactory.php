<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\PropertyAccessor;

use Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor;
use Psr\Container\ContainerInterface;

class ReflectionPropertyAccessorFactory
{
    /*
        <service id="nelmio_alice.property_accessor.reflection"
                 class="Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor">
           <argument type="service" id="nelmio_alice.property_accessor.std" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ReflectionPropertyAccessor
    {
        return new ReflectionPropertyAccessor(
            $container->get('nelmio_alice.property_accessor.std')
        );
    }
}
