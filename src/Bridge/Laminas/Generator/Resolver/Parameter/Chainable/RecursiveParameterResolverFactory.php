<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;
use Psr\Container\ContainerInterface;

class RecursiveParameterResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.parameter.chainable.string_parameter_resolver" />
            <argument>%nelmio_alice.loading_limit%</argument>

            <tag name="nelmio_alice.generator.resolver.parameter.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): RecursiveParameterResolver
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        return new RecursiveParameterResolver(
            $container->get('nelmio_alice.generator.resolver.parameter.chainable.string_parameter_resolver'),
            $aliceConfig['loading_limit']
        );
    }
}
