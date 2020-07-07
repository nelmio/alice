<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver;
use Psr\Container\ContainerInterface;

class OptionalValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.optional_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): OptionalValueResolver
    {
        return new OptionalValueResolver();
    }
}
