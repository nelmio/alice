<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable;

use Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator;
use Psr\Container\ContainerInterface;

class StaticFactoryInstantiatorFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.chainable.static_factory_instantiator"
                 class="Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator">
            <argument type="service" id="nelmio_alice.generator.named_arguments_resolver" />
            <tag name="nelmio_alice.generator.instantiator.chainable_instantiator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): StaticFactoryInstantiator
    {
        return new StaticFactoryInstantiator(
            $container->get('nelmio_alice.generator.named_arguments_resolver')
        );
    }
}
