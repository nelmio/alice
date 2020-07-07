<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver;
use Psr\Container\ContainerInterface;

class StaticParameterResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter.chainable.static_parameter_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver">
            <tag name="nelmio_alice.generator.resolver.parameter.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): StaticParameterResolver
    {
        return new StaticParameterResolver();
    }
}
