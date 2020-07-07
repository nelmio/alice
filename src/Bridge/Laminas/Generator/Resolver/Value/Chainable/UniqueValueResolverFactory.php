<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Psr\Container\ContainerInterface;

class UniqueValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.unique_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.value.unique_values_pool" />
            <argument>null</argument>
            <argument>%nelmio_alice.max_unique_values_retry%</argument>

            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): UniqueValueResolver
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        return new UniqueValueResolver(
            $container->get('nelmio_alice.generator.resolver.value.unique_values_pool'),
            null,
            $aliceConfig['max_unique_values_retry']
        );
    }
}
