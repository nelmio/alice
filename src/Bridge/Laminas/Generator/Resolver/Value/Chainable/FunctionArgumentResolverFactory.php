<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver;
use Psr\Container\ContainerInterface;

class FunctionArgumentResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.function_argument_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.value.chainable.php_value_resolver" />

            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FunctionCallArgumentResolver
    {
        return new FunctionCallArgumentResolver(
            $container->get('nelmio_alice.generator.resolver.value.chainable.php_value_resolver')
        );
    }
}
