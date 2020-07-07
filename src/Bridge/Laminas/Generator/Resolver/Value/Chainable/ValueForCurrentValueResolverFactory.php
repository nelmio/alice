<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolver;
use Psr\Container\ContainerInterface;

class ValueForCurrentValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.value_for_current_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ValueForCurrentValueResolver
    {
        return new ValueForCurrentValueResolver();
    }
}
