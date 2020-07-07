<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver;
use Psr\Container\ContainerInterface;

class StringParameterResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter.chainable.string_parameter_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver" />
    */
    public function __invoke(ContainerInterface $container): StringParameterResolver
    {
        return new StringParameterResolver();
    }
}
