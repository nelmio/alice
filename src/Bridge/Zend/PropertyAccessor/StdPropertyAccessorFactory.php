<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\PropertyAccessor;

use Nelmio\Alice\PropertyAccess\StdPropertyAccessor;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class StdPropertyAccessorFactory
{
    /*
        <service id="nelmio_alice.property_accessor.std"
                 class="Nelmio\Alice\PropertyAccess\StdPropertyAccessor">
           <argument type="service" id="property_accessor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): StdPropertyAccessor
    {
        return new StdPropertyAccessor(
            new PropertyAccessor()
        );
    }
}
