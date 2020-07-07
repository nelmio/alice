<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\ParameterBag;

use Nelmio\Alice\Generator\Resolver\Parameter\RemoveConflictingParametersParameterBagResolver;
use Psr\Container\ContainerInterface;

class RemoveConflictingParametersFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter_bag.remove_conflicting_parameters"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\RemoveConflictingParametersParameterBagResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.parameter_bag.simple" />
        </service>
    */
    public function __invoke(ContainerInterface $container): RemoveConflictingParametersParameterBagResolver
    {
        return new RemoveConflictingParametersParameterBagResolver(
            $container->get('nelmio_alice.generator.resolver.parameter_bag.simple')
        );
    }
}
