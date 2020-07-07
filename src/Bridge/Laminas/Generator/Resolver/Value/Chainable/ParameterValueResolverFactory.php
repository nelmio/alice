<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver;
use Psr\Container\ContainerInterface;

class ParameterValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.parameter_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): ParameterValueResolver
    {
        return new ParameterValueResolver();
    }
}
