<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\ParameterBag;

use Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver;
use Psr\Container\ContainerInterface;

class SimpleFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter_bag.simple"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.parameter.registry" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleParameterBagResolver
    {
        return new SimpleParameterBagResolver(
            $container->get('nelmio_alice.generator.resolver.parameter.registry')
        );
    }
}
