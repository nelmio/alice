<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable;

use Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator;
use Psr\Container\ContainerInterface;

class NullConstructorInstantiatorFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.chainable.null_constructor_instantiator"
                 class="Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator">
            <tag name="nelmio_alice.generator.instantiator.chainable_instantiator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): NullConstructorInstantiator
    {
        return new NullConstructorInstantiator();
    }
}
