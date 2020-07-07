<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Instantiator\Chainable;

use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;
use Psr\Container\ContainerInterface;

class NoCallerMethodInstantiatorFactory
{
    /*
        <service id="nelmio_alice.generator.instantiator.chainable.no_caller_method_instantiator"
                 class="Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator">
            <argument type="service" id="nelmio_alice.generator.named_arguments_resolver" />
            <tag name="nelmio_alice.generator.instantiator.chainable_instantiator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): NoCallerMethodCallInstantiator
    {
        return new NoCallerMethodCallInstantiator(
            $container->get('nelmio_alice.generator.named_arguments_resolver')
        );
    }
}
