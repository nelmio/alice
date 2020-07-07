<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver;
use Psr\Container\ContainerInterface;

class ListValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.list_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ListValueResolver
    {
        return new ListValueResolver();
    }
}
