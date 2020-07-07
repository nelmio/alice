<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable;

use Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator;
use Psr\Container\ContainerInterface;

class NoMethodCallInstantiatorFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.chainable.no_method_call_instantiator"
                 class="Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator">
            <tag name="nelmio_alice.generator.instantiator.chainable_instantiator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): NoMethodCallInstantiator
    {
        return new NoMethodCallInstantiator();
    }
}
