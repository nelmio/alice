<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value;

use Nelmio\Alice\Generator\Resolver\Value\ValueResolverRegistry;
use Psr\Container\ContainerInterface;

class ValueResolverRegistryFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.registry"
                 class="Nelmio\Alice\Generator\Resolver\Value\ValueResolverRegistry">
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): ValueResolverRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $valueResolvers = array_map(
            [$container, 'get'],
            $aliceConfig['generator']['resolver']['value']['chainable_resolver']
        );

        return new ValueResolverRegistry($valueResolvers);
    }
}
