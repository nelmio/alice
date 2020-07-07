<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver;
use Psr\Container\ContainerInterface;

class DynamicArrayValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.dynamic_array_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): DynamicArrayValueResolver
    {
        return new DynamicArrayValueResolver();
    }
}
