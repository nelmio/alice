<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver;
use Psr\Container\ContainerInterface;

class FakerValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.faker_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver">
            <argument type="service" id="nelmio_alice.faker.generator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FakerFunctionCallValueResolver
    {
        return new FakerFunctionCallValueResolver(
            $container->get('nelmio_alice.faker.generator')
        );
    }
}
