<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\ArrayValueResolver;
use Psr\Container\ContainerInterface;

class ArrayValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.array_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\ArrayValueResolver">

            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ArrayValueResolver
    {
        return new ArrayValueResolver();
    }
}
