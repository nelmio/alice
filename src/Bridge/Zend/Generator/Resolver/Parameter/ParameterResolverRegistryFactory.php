<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry;
use Psr\Container\ContainerInterface;

class ParameterResolverRegistryFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter.registry"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry">
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): ParameterResolverRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $parameterResolvers = array_map(
            [$container, 'get'],
            $aliceConfig['generator']['resolver']['parameter']['chainable_resolver']
        );

        return new ParameterResolverRegistry($parameterResolvers);
    }
}
