<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver;
use Psr\Container\ContainerInterface;

class ArrayParameterResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter.chainable.array_parameter_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver">
            <tag name="nelmio_alice.generator.resolver.parameter.chainable_resolver" />
        </service>

    */
    public function __invoke(ContainerInterface $container): ArrayParameterResolver
    {
        return new ArrayParameterResolver();
    }
}
