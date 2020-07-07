<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver;
use Psr\Container\ContainerInterface;

class VariableValueResolverFactory
{
    /*
       <service id="nelmio_alice.generator.resolver.value.chainable.variable_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): VariableValueResolver
    {
        return new VariableValueResolver();
    }
}
